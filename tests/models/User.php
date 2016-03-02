<?php

namespace tests\models;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $name
 */
class User extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }
}
