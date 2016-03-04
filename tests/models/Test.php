<?php

namespace tests\models;

use arogachev\ManyToMany\behaviors\ManyToManyBehavior;
use arogachev\ManyToMany\validators\ManyToManyValidator;
use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $name
 *
 * @property User[] $usersViaTable
 * @property User[] $usersViaRelation
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
            'manyToMany' => [
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
     * @return \yii\db\ActiveQuery
     */
    public function getUsersViaTable()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
            ->viaTable('tests_users', ['test_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTestUsers()
    {
        return $this->hasMany(TestUser::className(), ['test_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsersViaRelation()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
            ->via('testUsers');
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
