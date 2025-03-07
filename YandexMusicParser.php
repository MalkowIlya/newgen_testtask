<?php

use app\models\Album;
use app\models\Musician;
use app\models\Track;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\DomCrawler\Crawler;

class YandexMusicParser
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }


    public function crawlTrack()
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $html = curl_exec($ch);
        curl_close($ch);

        if ($html) {
            $crawler = new Crawler($html);
            $mainCrawler = $crawler->filter('.d-generic-page-head__main');

            $artist = $mainCrawler->filter('.page-artist__title.typo-h1')->text();
            $subsCount = $mainCrawler
                ->filter('.d-like.d-like_theme-count .d-button__label')
                ->text();
            $listenerCount = $mainCrawler
                ->filter(".page-artist__summary.typo.deco-typo-secondary")
                ->children('span')
                ->first()
                ->text();

            $artistData = [
                'name' => $this->clearSpaceChar($artist),
                'subsCount' => $this->clearSpaceChar($subsCount),
                'listenerCount' => $this->clearSpaceChar($listenerCount),
            ];

            $trackList = [];

            $crawler->filter('body div.d-track')
                ->each(function (Crawler $node, int $i) use (&$trackList) {
                    $trackList[] = [
                        'name' => trim($node->filter('div.d-track__name')->text()),
                        'album' => $this->clearSpaceChar($node->filter('div.d-track__meta')->text()),
                        'time' => trim($node->filter('.typo-track.deco-typo-secondary')->text()),
                    ];
                });


            $artistData['trackList'] = $trackList;

            $this->saveData($artistData);
        }
    }

    /**
     * @return Exception|void
     */
    private function parseTrackChrome()
    {
        try {
            $browser = Browsershot::url($this->url);
            $browser
                ->setRemoteInstance('chrome')
                ->windowSize(1920, 1080)
                ->noSandbox()
                ->waitUntilNetworkIdle()
                ->setNodeBinary('/usr/bin/node')
                ->setNpmBinary('/usr/bin/npm')
                ->setOption('addScriptTag', json_encode([
                    'content' => "(async () => {
            let totalHeight = 0;
            let distance = 100;
            let scrollDelay = 100;
            while(totalHeight < document.body.scrollHeight) {
                window.scrollBy(0, distance);
                totalHeight += distance;
                await new Promise(resolve => setTimeout(resolve, scrollDelay));
            }
        })()"
                ]))
                ->setDelay(5000)
                ->fullPage();

        } catch (\Exception $e) {
            return $e;
        } finally {
            $browser->close();
        }
    }

    /**
     * @return Exception|void
     */
    private function saveData(array $data)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$musicianDb = Musician::find()->where(['name' => $data['name']])->one()) {
                $musicianDb = new Musician();
                $musicianDb->name = $data['name'];
                $musicianDb->subscribers_count = $data['subsCount'];
                $musicianDb->listeners_count = $data['listenerCount'];

                if (!$musicianDb->save()) {
                    throw new \Exception('cant save musician');
                }
            }

            $albums = Album::find()->where(['musician_id' => $musicianDb->id])->indexBy('name')->all();
            foreach ($data['trackList'] as $track) {
                if (!isset($albums[$track['album']])) {
                    $albumDb = new Album();

                    $albumDb->name = $track['album'];
                    $albumDb->musician_id = $musicianDb->id;

                    $albumDb->save();

                    $albums[$albumDb->name] = $albumDb;
                } else {
                    $albumDb = $albums[$track['album']];
                }

                $trackDb = Track::find()->where(['name' => $track['name'], 'album_id' => $albumDb->id])->one();

                if (!$trackDb) {
                    $trackDb = new Track();
                    $trackDb->name = $track['name'];
                    $trackDb->album_id = $albumDb->id;

                    $trackDb->duration = $this->parseTime($track['time']);

                    $trackDb->save();
                }
            }

            $musicianDb->albums_count = count($albums);
            $musicianDb->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            return $e;
        }
    }

    private function parseTime(string $time): int
    {
        $seconds = 0;
        $arrTime = explode(':', $time);
        if (isset($arrTime[0])) {
            $seconds = (int)$arrTime[0] * 60;
        }
        if (isset($arrTime[1])) {
            $seconds += (int)$arrTime[1];
        }

        return $seconds;
    }

    private function clearSpaceChar(string $string): string
    {
        return preg_replace("/\s+/", "", $string);
    }
}