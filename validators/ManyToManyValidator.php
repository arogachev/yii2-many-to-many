<?php

namespace arogachev\ManyToMany\validators;

use arogachev\ManyToMany\behaviors\ManyToManyBehavior;
use Yii;
use yii\base\InvalidConfigException;
use yii\i18n\PhpMessageSource;
use yii\validators\Validator;

class ManyToManyValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Yii::setAlias('@many-to-many', dirname(__DIR__));
        Yii::$app->i18n->translations['many-to-many'] = [
            'class' => PhpMessageSource::className(),
            'basePath' => '@many-to-many/messages',
        ];
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        if (!is_array($model->$attribute)) {
            $model->addError($attribute, Yii::t('many-to-many', '{attribute} must be a list.', [
                'attribute' => $label,
            ]));

            return;
        }

        /* @var $behavior null|ManyToManyBehavior */
        $behavior = null;

        foreach ($model->behaviors as $key => $attachedBehavior) {
            if ($attachedBehavior::className() == ManyToManyBehavior::className()) {
                $behavior = $attachedBehavior;

                break;
            }
        }

        if (!$behavior) {
            throw new InvalidConfigException("Behavior not detected.");
        }

        /* @var $relation null|\arogachev\ManyToMany\components\ManyToManyRelation */
        $relation = null;

        foreach ($behavior->getManyToManyRelations() as $attachedRelation) {
            if ($attachedRelation->editableAttribute == $attribute) {
                $relation = $attachedRelation;

                break;
            }
        }

        if (!$relation) {
            throw new InvalidConfigException("Relation not detected.");
        }

        $primaryKeys = $model->$attribute;

        if (!$primaryKeys) {
            return;
        }

        /* @var $relatedModel \yii\db\ActiveRecord */
        $relatedModel = $relation->relatedModel;
        $relatedModelPk = $relatedModel::primaryKey()[0];
        $relatedModelsCount = $relatedModel::find()->where([$relatedModelPk => $primaryKeys])->count();

        if (count($primaryKeys) != $relatedModelsCount) {
            $error = 'There are nonexistent elements in {attribute} list.';
            $model->addError($attribute, Yii::t('many-to-many', $error, ['attribute' => $label]));
        }
    }
}
