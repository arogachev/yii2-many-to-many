<?php

namespace tests;

use arogachev\ManyToMany\behaviors\ManyToManyBehavior;
use tests\models\Test;
use tests\models\User;

/**
 * These tests use 3 config variations:
 * @see ManyToManyBehaviorTest::testValidate()
 * @see ManyToManyBehaviorTest::testCreate()
 * @see ManyToManyBehaviorTest::testUpdateNotFilled()
 * @see ManyToManyBehaviorTest::testUpdateCreate()
 * @see ManyToManyBehaviorTest::testUpdateAdd()
 * @see ManyToManyBehaviorTest::testUpdateDelete()
 * @see ManyToManyBehaviorTest::testUpdateAndDelete()
 * @see ManyToManyBehaviorTest::testDelete()
 * @see ManyToManyBehaviorTest::testAutoFill()
 * @see ManyToManyBehaviorTest::testFill()
 * @see ManyToManyBehaviorTest::testPrimaryKeysDiff()
 */
class ManyToManyBehaviorTest extends DatabaseTestCase
{
    /**
     * Config test
     */
    public function testConfig()
    {
        $test = new Test;
        $test = $this->useRelationViaTable($test);
        $relation = $test->getManyToManyRelation('usersViaTable');
        $this->assertRelationConfigsEqual($relation);

        $test = $this->useRelationViaRelation($test);
        $relation = $test->getManyToManyRelation('usersViaRelation');
        $this->assertRelationConfigsEqual($relation);
    }

    /**
     * Validate test
     * @param Test|null $test
     */
    public function testValidate($test = null)
    {
        $test = $test ? $test : $this->findTestModel(2);
        $test->editableUsers = '1, 2';
        $this->assertEquals(false, $test->save());
        $this->assertEquals(['Editable Users must be a list.'], $test->getErrors('editableUsers'));

        $test->editableUsers = [1, 10];
        $this->assertEquals(false, $test->save());
        $this->assertEquals(
            ['There are nonexistent elements in Editable Users list.'],
            $test->getErrors('editableUsers')
        );
    }

    /**
     * Validate test using relation via table
     */
    public function testValidateUsingRelationViaTable()
    {
        $test = $this->findTestModel(2);
        $test = $this->useRelationViaTable($test);
        $this->testValidate($test);
    }

    /**
     * Validate test using relation via relation
     */
    public function testValidateUsingRelationViaRelation()
    {
        $test = $this->findTestModel(2);
        $test = $this->useRelationViaRelation($test);
        $this->testValidate($test);
    }

    /**
     * Create test
     * @param Test|null $test
     */
    public function testCreate($test = null)
    {
        $test = $test ? $test : new Test;
        $test->name = 'Job Test';
        $test->editableUsers = [1, 2];
        $test->save();
        $this->assertTestsEqual('create');
        $this->assertTestsUsersEqual('create');
    }

    /**
     * Create test using relation via table
     */
    public function testCreateUsingRelationViaTable()
    {
        $test = new Test;
        $test = $this->useRelationViaTable($test);
        $this->testCreate($test);
    }

    /**
     * Create test using relation via relation
     */
    public function testCreateUsingRelationViaRelation()
    {
        $test = new Test;
        $test = $this->useRelationViaRelation($test);
        $this->testCreate($test);
    }

    /**
     * Update not filled test
     * @param Test|null $test
     */
    public function testUpdateNotFilled($test = null)
    {
        $test = $test ? $test : $this->findTestModel(1, ['autoFill' => false]);
        $test->editableUsers = [];
        $test->save();
        $this->assertTestsUsersEqual('initial');
    }

    /**
     * Update not filled test using relation via table
     */
    public function testUpdateNotFilledUsingRelationViaTable()
    {
        $additionalConfig = ['autoFill' => false];
        $test = $this->findTestModel(1, $additionalConfig);
        $test = $this->useRelationViaTable($test, $additionalConfig);
        $this->testUpdateNotFilled($test);
    }

    /**
     * Update not filled test using relation via relation
     */
    public function testUpdateNotFilledUsingRelationViaRelation()
    {
        $additionalConfig = ['autoFill' => false];
        $test = $this->findTestModel(1, $additionalConfig);
        $test = $this->useRelationViaRelation($test, $additionalConfig);
        $this->testUpdateNotFilled($test);
    }

    /**
     * Update (create) test
     * @param Test|null $test
     */
    public function testUpdateCreate($test = null)
    {
        $test = $test ? $test : $this->findTestModel(2);
        $test->editableUsers = [1, 2];
        $test->save();
        $this->assertTestsUsersEqual('update-create');
    }

    /**
     * Update (create) test using relation via table
     */
    public function testUpdateCreateUsingRelationViaTable()
    {
        $test = $this->findTestModel(2);
        $test = $this->useRelationViaTable($test);
        $this->testUpdateCreate($test);
    }

    /**
     * Update (create) test using relation via relation
     */
    public function testUpdateCreateUsingRelationViaRelation()
    {
        $test = $this->findTestModel(2);
        $test = $this->useRelationViaRelation($test);
        $this->testUpdateCreate($test);
    }

    /**
     * Update (add) test
     * @param Test|null $test
     */
    public function testUpdateAdd($test = null)
    {
        $test = $test ? $test : $this->findTestModel(1);
        $test->editableUsers = [1, 2, 3];
        $test->save();

        $this->assertTestsUsersEqual('update-add');
    }

    /**
     * Update (add) test using relation via table
     */
    public function testUpdateAddUsingRelationViaTable()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaTable($test);
        $this->testUpdateAdd($test);
    }

    /**
     * Update (add) test using relation via relation
     */
    public function testUpdateAddUsingRelationViaRelation()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaRelation($test);
        $this->testUpdateAdd($test);
    }

    /**
     * Update (delete) test
     * @param Test|null $test
     */
    public function testUpdateDelete($test = null)
    {
        $test = $test ? $test : $this->findTestModel(1);
        $test->editableUsers = [1];
        $test->save();

        $this->assertTestsUsersEqual('update-delete');
    }

    /**
     * Update (delete) test using relation via table
     */
    public function testUpdateDeleteUsingRelationViaTable()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaTable($test);
        $this->testUpdateDelete($test);
    }

    /**
     * Update (delete) test using relation via relation
     */
    public function testUpdateDeleteUsingRelationViaRelation()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaRelation($test);
        $this->testUpdateDelete($test);
    }

    /**
     * Update (add and delete) test
     * @param Test|null $test
     */
    public function testUpdateAddAndDelete($test = null)
    {
        $test = $test ? $test : $this->findTestModel(1);
        $test->editableUsers = [1, 3];
        $test->save();

        $this->assertTestsUsersEqual('update-add-and-delete');
    }

    /**
     * Update (add and delete) test using relation via table
     */
    public function testUpdateAddAndDeleteUsingRelationViaTable()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaTable($test);
        $this->testUpdateAddAndDelete($test);
    }

    /**
     * Update (add and delete) test using relation via relation
     */
    public function testUpdateAddAndDeleteUsingRelationViaRelation()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaRelation($test);
        $this->testUpdateAddAndDelete($test);
    }

    /**
     * Delete test
     * @param Test|null $test
     */
    public function testDelete($test = null)
    {
        $test = $test ? $test : $this->findTestModel(1);
        $test->editableUsers = [];
        $test->save();

        $this->assertTestsUsersEqual('delete');
    }

    /**
     * Delete test using relation via table
     */
    public function testDeleteUsingRelationViaTable()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaTable($test);
        $this->testDelete($test);
    }

    /**
     * Delete test using relation via relation
     */
    public function testDeleteUsingRelationViaRelation()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaRelation($test);
        $this->testDelete($test);
    }

    /**
     * Auto fill test
     * @param Test|null $test
     */
    public function testAutoFill($test = null)
    {
        $test = $test ? $test : $this->findTestModel(1);
        $this->assertEquals([1, 2], $test->editableUsers);

        $test = $this->findTestModel(1, ['autoFill' => false]);
        $this->assertEquals([], $test->editableUsers);

        $test = $this->findTestModel(1, ['autoFill' => function ($model) {
            return true;
        }]);
        $this->assertEquals([1, 2], $test->editableUsers);
    }

    /**
     * Auto fill test using relation via table
     */
    public function testAutoFillUsingRelationViaTable()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaTable($test);
        $this->testAutoFill($test);
    }

    /**
     * Auto fill test using relation via relation
     */
    public function testAutoFillUsingRelationViaRelation()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaRelation($test);
        $this->testAutoFill($test);
    }

    /**
     * Fill test
     * @param Test|null $test
     */
    public function testFill($test = null)
    {
        $test = $test ? $test : $this->findTestModel(1, ['autoFill' => false]);
        $this->assertEquals([], $test->editableUsers);

        $test->getManyToManyRelation('tests_users')->fill();
        $this->assertEquals([1, 2], $test->editableUsers);
    }

    /**
     * Fill test using relation via table
     */
    public function testFillUsingRelationViaTable()
    {
        $additionalConfig = ['autoFill' => false];
        $test = $this->findTestModel(1, $additionalConfig);
        $test = $this->useRelationViaTable($test, $additionalConfig);
        $this->testFill($test);
    }

    /**
     * Fill test using relation via relation
     */
    public function testFillUsingRelationViaRelation()
    {
        $additionalConfig = ['autoFill' => false];
        $test = $this->findTestModel(1, $additionalConfig);
        $test = $this->useRelationViaRelation($test, $additionalConfig);
        $this->testFill($test);
    }

    /**
     * Test added and deleted primary keys
     * @param Test|null $test
     */
    public function testPrimaryKeysDiff($test = null)
    {
        $test = $test ? $test : $this->findTestModel(1);
        $test->editableUsers = [1, 3];
        $test->save();

        $this->assertEquals([3], $test->getManyToManyRelation('tests_users')->getAddedPrimaryKeys());
        $this->assertEquals([2], $test->getManyToManyRelation('tests_users')->getDeletedPrimaryKeys());
    }

    /**
     * Test added and deleted primary keys using relation via table
     */
    public function testPrimaryKeysDiffUsingRelationViaTable()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaTable($test);
        $this->testPrimaryKeysDiff($test);
    }

    /**
     * Test added and deleted primary keys using relation via relation
     */
    public function testPrimaryKeysDiffUsingRelationViaRelation()
    {
        $test = $this->findTestModel(1);
        $test = $this->useRelationViaRelation($test);
        $this->testPrimaryKeysDiff($test);
    }

    /**
     * Find test model by id and optionally change users relation config
     * @param integer $id
     * @param array $additionalUsersRelationConfig
     * @return Test|\arogachev\ManyToMany\behaviors\ManyToManyBehavior
     */
    protected function findTestModel($id, $additionalUsersRelationConfig = [])
    {
        Test::$additionalUsersRelationConfig = $additionalUsersRelationConfig;

        return Test::findOne($id);
    }

    /**
     * Use alternative config - relation via table
     * @param Test|\arogachev\ManyToMany\behaviors\ManyToManyBehavior $test
     * @param array $additionalUsersRelationConfig
     * @return Test|\arogachev\ManyToMany\behaviors\ManyToManyBehavior
     */
    protected function useRelationViaTable($test, $additionalUsersRelationConfig = [])
    {
        $test->attachBehavior('manyToMany', [
            'class' => ManyToManyBehavior::className(),
            'relations' => [
                array_merge(
                    [
                        'name' => 'usersViaTable',
                        'editableAttribute' => 'editableUsers',
                    ],
                    $additionalUsersRelationConfig
                ),
            ]
        ]);
        $test->customInit();
        if (!$test->isNewRecord) {
            $test->afterFind();
        }

        return $test;
    }

    /**
     * Use alternative config - relation via relation
     * @param Test|\arogachev\ManyToMany\behaviors\ManyToManyBehavior $test
     * @param array $additionalUsersRelationConfig
     * @return Test|\arogachev\ManyToMany\behaviors\ManyToManyBehavior
     */
    protected function useRelationViaRelation($test, $additionalUsersRelationConfig = [])
    {
        $test->attachBehavior('manyToMany', [
            'class' => ManyToManyBehavior::className(),
            'relations' => [
                array_merge(
                    [
                        'name' => 'usersViaRelation',
                        'editableAttribute' => 'editableUsers',
                    ],
                    $additionalUsersRelationConfig
                ),
            ]
        ]);
        $test->customInit();
        if (!$test->isNewRecord) {
            $test->afterFind();
        }

        return $test;
    }

    /**
     * Check if relation configs are equal
     * @param \arogachev\ManyToMany\components\ManyToManyRelation $relation
     */
    protected function assertRelationConfigsEqual($relation)
    {
        $this->assertEquals($relation->table, 'tests_users');
        $this->assertEquals($relation->ownAttribute, 'test_id');
        $this->assertEquals($relation->relatedModel, User::className());
        $this->assertEquals($relation->relatedAttribute, 'user_id');
    }

    /**
     * Check if tests tables are equal
     * @param string $dataSetName
     */
    protected function assertTestsEqual($dataSetName)
    {
        $dataSet = $this->getYamlDataSet($dataSetName);
        $testsTable = $this->getConnection()->createQueryTable(
            'tests',
            'SELECT * FROM `tests` ORDER BY `id`'
        );
        $this->assertTablesEqual($dataSet->getTable('tests'), $testsTable);
    }

    /**
     * Check if tests-users many-to-many tables are equal
     * @param string $dataSetName
     */
    protected function assertTestsUsersEqual($dataSetName)
    {
        $dataSet = $this->getYamlDataSet($dataSetName);
        $testsUsersTable = $this->getConnection()->createQueryTable(
            'tests_users',
            'SELECT * FROM `tests_users` ORDER BY `test_id`, `user_id`'
        );
        $this->assertTablesEqual($dataSet->getTable('tests_users'), $testsUsersTable);
    }
}
