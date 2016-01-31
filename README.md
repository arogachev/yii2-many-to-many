# Yii 2 Many-to-many

Implementation of [Many-to-many relationship](http://en.wikipedia.org/wiki/Many-to-many_%28data_model%29)
for Yii 2 framework.

- [Installation](#installation)
- [Features](#features)
- [Creating editable attribute](#creating-editable-attribute)
- [Attaching and configuring behavior](#attaching-and-configuring-behavior)
- [Adding attribute as safe](#adding-attribute-as-safe)
- [Adding control to view](#adding-control-to-view)
- [Additional features](#additional-features)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist arogachev/yii2-many-to-many
```

or add

```
"arogachev/yii2-many-to-many": "*"
```

to the require section of your `composer.json` file.

## Features

- Configuring using existing ```hasMany``` relations
- Multiple relations
- No extra queries. For example, if initially model has 100 related records,
after adding just one, exactly one row will be inserted. If nothing was changed, no queries will be executed.
- Auto filling of editable attribute for given route(s)
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
                    'editableAttribute' => 'users', // Editable attribute name
                    'table' => 'tests_to_users', // Name of the junction table
                    'ownAttribute' => 'test_id', // Name of the column in junction table that represents current model
                    'relatedModel' => User::className(), // Related model class
                    'relatedAttribute' => 'user_id', // Name of the column in junction table that represents related model
                    'fillingRoute' => 'tests/default/update', // Full route name (including module id if it's inside module) for auto filling editable attribute with existing data. You can also pass array of routes.
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
                    // These are the same as in previous example
                    'editableAttribute' => 'editableUsers',
                    'fillingRoute' => 'tests/default/update',
                ],
            ],
        ],
    ];
}
```

Additional many-to-many relations can be added exactly the same.
Note that even for one relation you should declare it as a part of `relations` section.

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

## Additional features

You can get added and deleted primary keys of related models for specific relation like so:

```php
$addedPrimaryKeys = $model->getManyToManyRelation('tags')->getAddedPrimaryKeys();
$deletedPrimaryKeys = $model->getManyToManyRelation('tags')->getDeletedPrimaryKeys();
```

Note that they are only available after the model was saved so you can access it after `$model->save()` call
or in `afterSave()` event handler.
