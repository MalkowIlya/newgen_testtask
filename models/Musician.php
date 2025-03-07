<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $subscribers_count
 * @property int $listeners_count
 * @property int $albums_count
 *
 * @property Album[] $albums
 */
class Musician extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'musician';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['subscribers_count', 'listeners_count', 'albums_count'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'subscribers_count' => 'Subscribers Count',
            'listeners_count' => 'Listeners Count',
            'albums_count' => 'Albums Count',
        ];
    }

    public function getAlbums(): ActiveQuery
    {
        return $this->hasMany(Album::className(), ['musician_id' => 'id']);
    }
}