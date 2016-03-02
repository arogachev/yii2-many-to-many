<?php

namespace tests\models;

use arogachev\ManyToMany\behaviors\ManyToManyBehavior;
use arogachev\ManyToMany\validators\ManyToManyValidator;
use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $name
 */
class Test extends ActiveRecord
{
    /**
     * @var array
     */
    public $editableUsers = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => ManyToManyBehavior::className(),
                'relations' => [
                    [
                        'editableAttribute' => 'editableUsers',
                        'table' => 'tests_users',
                        'ownAttribute' => 'test_id',
                        'relatedModel' => User::className(),
                        'relatedAttribute' => 'user_id',
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tests';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['editableUsers', ManyToManyValidator::className()],
        ];
    }
}
