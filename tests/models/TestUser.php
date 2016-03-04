<?php

namespace tests\models;

use yii\db\ActiveRecord;

/**
 * @property integer $test_id
 * @property integer $user_id
 */
class TestUser extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tests_users';
    }
}
