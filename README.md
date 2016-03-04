# Yii 2 Many-to-many

Implementation of [Many-to-many relationship](http://en.wikipedia.org/wiki/Many-to-many_%28data_model%29)
for Yii 2 framework.

[![Latest Stable Version](https://poser.pugx.org/arogachev/yii2-many-to-many/v/stable)](https://packagist.org/packages/arogachev/yii2-many-to-many)
[![Total Downloads](https://poser.pugx.org/arogachev/yii2-many-to-many/downloads)](https://packagist.org/packages/arogachev/yii2-many-to-many)
[![Latest Unstable Version](https://poser.pugx.org/arogachev/yii2-many-to-many/v/unstable)](https://packagist.org/packages/arogachev/yii2-many-to-many)
[![License](https://poser.pugx.org/arogachev/yii2-many-to-many/license)](https://packagist.org/packages/arogachev/yii2-many-to-many)

- [Installation](#installation)
- [Features](#features)
- [Creating editable attribute](#creating-editable-attribute)
- [Attaching and configuring behavior](#attaching-and-configuring-behavior)
- [Filling relations](#filling-relations)
- [Saving relations without massive assignment](#saving-relations-without-massive-assignment)
- [Adding attribute as safe](#adding-attribute-as-safe)
- [Adding control to view](#adding-control-to-view)
- [Relation features](#relation-features)
- [Running tests](#running-tests)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist arogachev/yii2-many-to-many
```

or add

```
"arogachev/yii2-many-to-many": "0.2.*"
```

to the require section of your `composer.json` file.

## Features

- Configuring using existing ```hasMany``` relations
- Multiple relations
- No extra queries. For example, if initially model has 100 related records,
after adding just one, exactly one row will be inserted. If nothing was changed, no queries will be executed.
- Auto filling of editable attribute
- Validator for checking if the received list is valid

## Creating editable attribute

Simply add public property to your `ActiveRecord` model like this:

```php
/**
 * @var array
 */
public $editableUsers = [];
```

It will store primary keys of related records during update.

## Attaching and configuring behavior

First way is to explicitly specify all parameters:

```php
use arogachev\ManyToMany\behaviors\ManyToManyBehavior;

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
                    'editableAttribute' => 'editableUsers', // Editable attribute name
                    'table' => 'tests_to_users', // Name of the junction table
                    'ownAttribute' => 'test_id', // Name of the column in junction table that represents current model
                    'relatedModel' => User::className(), // Related model class
                    'relatedAttribute' => 'user_id', // Name of the column in junction table that represents related model
                ],
            ],
        ],
    ];
}
```

But more often we also need to display related models,
so it's better to define relation for that and use it for both display and behavior configuration.
Both ways (```via``` and ```viaTable```) are considered valid:

Using ```viaTable```:

```php
/**
 * @return \yii\db\ActiveQuery
 */
public function getUsers()
{
    return $this->hasMany(User::className(), ['id' => 'user_id'])
        ->viaTable('tests_to_users', ['test_id' => 'id'])
        ->orderBy('name');
}
```

Using ```via``` (requires additional model for junction table):

```php
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
public function getUsers()
{
    return $this->hasMany(User::className(), ['id' => 'user_id'])
        ->via('testUsers')
        ->orderBy('name');
}
```

Order is not required.

Then just pass the name of this relation and all other parameters will be fetched automatically.

```php
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
                    'name' => 'users',
                    // This is the same as in previous example
                    'editableAttribute' => 'editableUsers',
                ],
            ],
        ],
    ];
}
```

Additional many-to-many relations can be added exactly the same.
Note that even for one relation you should declare it as a part of `relations` section.

## Filling relations

By default, `editableAttribute` of each found model will be populated with ids of related models (eager loading is
used). If you want more manual control, prevent extra queries, disable `autoFill` option:

```php
'autoFill' => false,
```

and fill it only when it's needed, for example in `update` action of controller. This is recommended way of using.

```php
public function actionUpdate($id)
{
    $model = $this->findModel($id);
    $model->getManyToManyRelation('users')->fill();
    // ...
}
```

Alternatively you can specify conditions of filling in closure:

```php
`autoFill` => function ($model) {
    return $model->scenario == Test::SCENARIO_UPDATE; // boolean value
}
```

Even it's possible to do something like this:

```php
`autoFill` => function ($model) {
    return Yii::$app->controller->route == 'tests/default/update';
}
```

but it's not recommended for usage because model is not appropriate place for handling routes.

## Saving relations without massive assignment

When creating model:

```php
$model = new Test;
$model->editableUsers = [1, 2];
$model->save();
```

When updating model (`'autoFill' => true`):

```php
$model = new Test;
$model->editableUsers = [1, 2];
$model->save();
```

When updating model (`'autoFill' => false`, manual filling):

```php
$model = new Test;
$model->getManyToManyRelation('users')->fill();
var_dump($model->editableUsers) // [1, 2]
$model->editableUsers = [1, 2, 3];
$model->save();
```

When updating model (`'autoFill' => false`, without manual filling):

```php
$model = new Test;
var_dump($model->editableUsers) // empty array
$model->save();
```

In this case many-to-many relations will stay untouched.

## Adding attribute as safe

Add editable attribute to model rules for massive assignment.

Either mark it as safe at least:

```php
public function rules()
{
    ['editableUsers', 'safe'],
}
```

Or use custom validator:

```php
use arogachev\ManyToMany\validators\ManyToManyValidator;

public function rules()
{
    ['editableUsers', ManyToManyValidator::className()],
}
```

Validator checks list for being array and containing only primary keys presented in related model.
It can not be used without attaching `ManyToManyBehavior`.

## Adding control to view

Add control to view for managing related list. Without extensions it can be done with multiple select:

```php
<?= $form->field($model, 'editableUsers')->dropDownList(User::getList(), ['multiple' => true]) ?>
```

Example of `getList()` method contents (it need to be placed in `User` model):

```php
use yii\helpers\ArrayHelper;

/**
 * @return array
 */
public static function getList()
{
    $models = static::find()->orderBy('name')->all();

    return ArrayHelper::map($models, 'id', 'name');
}
```

## Relation features

You can access many-to-many relation like so:

```php
$relation = $model->getManyToManyRelation('users');
```

`users` can be value of either `name` or `table` relation property specified in config.

You can fill `editableAttribute` with ids of related records like so:

```php
$model->getManyToManyRelation('users')->fill();
```

You can get added and deleted primary keys of related models for specific relation like so:

```php
$addedPrimaryKeys = $model->getManyToManyRelation('users')->getAddedPrimaryKeys();
$deletedPrimaryKeys = $model->getManyToManyRelation('users')->getDeletedPrimaryKeys();
```

Note that they are only available after the model was saved so you can access it after `$model->save()` call
or in `afterSave()` event handler.

## Running tests

Install dependencies:

```
composer install
```

Add database config (`tests/config/db-local.php` file) with following contents:

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2_many_to_many',
    'username' => 'root',
    'password' => '',
];
```

You can change `dbname`, `username` and `password` how you want. Make sure create database and user before running
tests.

Run tests:

```
vendor/bin/phpunit
```
