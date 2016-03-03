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
     * @var array
     */
    public static $additionalUsersRelationConfig = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => ManyToManyBehavior::className(),
                'relations' => [
                    array_merge(
                        [
                            'editableAttribute' => 'editableUsers',
                            'table' => 'tests_users',
                            'ownAttribute' => 'test_id',
                            'relatedModel' => User::className(),
                            'relatedAttribute' => 'user_id',
                        ],
                        self::$additionalUsersRelationConfig
                    ),
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

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

        self::$additionalUsersRelationConfig = [];
    }
}
