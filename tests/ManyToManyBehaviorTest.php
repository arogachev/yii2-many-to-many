<?php

namespace tests;

class ManyToManyBehaviorTest extends DatabaseTestCase
{
    /**
     * Validation test
     */
    public function testValidation()
    {
        $test = $this->findTestModel(2);
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
     * Create test
     */
    public function testCreate()
    {
        $test = $this->findTestModel(2);
        $test->editableUsers = [1, 2];
        $test->save();
        $this->assertTestsUsersEqual('create');
    }

    /**
     * Update (add) test
     */
    public function testUpdateAdd()
    {
        $test = $this->findTestModel(1);
        $test->editableUsers = [1, 2, 3];
        $test->save();

        $this->assertTestsUsersEqual('update-add');
    }

    /**
     * Update (delete) test
     */
    public function testUpdateDelete()
    {
        $test = $this->findTestModel(1);
        $test->editableUsers = [1];
        $test->save();

        $this->assertTestsUsersEqual('update-delete');
    }

    /**
     * Update (add and delete) test
     */
    public function testUpdateAddAndDelete()
    {
        $test = $this->findTestModel(1);
        $test->editableUsers = [1, 3];
        $test->save();

        $this->assertTestsUsersEqual('update-add-and-delete');
    }

    /**
     * Delete test
     */
    public function testDelete()
    {
        $test = $this->findTestModel(1);
        $test->editableUsers = [];
        $test->save();

        $this->assertTestsUsersEqual('delete');
    }

    /**
     * Test added and deleted primary keys
     */
    public function testPrimaryKeysDiff()
    {
        $test = $this->findTestModel(1);
        $test->editableUsers = [1, 3];
        $test->save();

        $this->assertEquals([3], $test->getManyToManyRelation('tests_users')->getAddedPrimaryKeys());
        $this->assertEquals([2], $test->getManyToManyRelation('tests_users')->getDeletedPrimaryKeys());
    }
}
