<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $musician_id
 * @property string $name
 *
 * @property Musician $musician
 * @property Track[] $tracks
 */
class Album extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'album';
    }


    public function rules(): array
    {
        return [
            [['musician_id', 'name'], 'required'],
            [['musician_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['musician_id'], 'exist', 'skipOnError' => true, 'targetClass' => Musician::className(), 'targetAttribute' => ['musician_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'musician_id' => 'Musician ID',
            'name' => 'Name',
        ];
    }

    public function getMusician(): ActiveQuery
    {
        return $this->hasOne(Musician::className(), ['id' => 'musician_id']);
    }

    public function getTracks(): ActiveQuery
    {
        return $this->hasMany(Track::className(), ['album_id' => 'id']);
    }
}