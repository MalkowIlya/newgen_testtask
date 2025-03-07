<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $album_id
 * @property string $name
 * @property double $duration
 *
 * @property Album $album
 */
class Track extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'track';
    }

    public function rules(): array
    {
        return [
            [['album_id', 'name'], 'required'],
            [['album_id'], 'integer'],
            [['duration'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['album_id'], 'exist', 'skipOnError' => true, 'targetClass' => Album::className(), 'targetAttribute' => ['album_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'album_id' => 'Album ID',
            'name' => 'Name',
            'duration' => 'Duration',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAlbum(): ActiveQuery
    {
        return $this->hasOne(Album::className(), ['id' => 'album_id']);
    }
}