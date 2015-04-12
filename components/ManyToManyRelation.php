<?php

namespace arogachev\ManyToMany\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ManyToManyRelation extends Object
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $editableAttribute;

    /**
     * @var string|array
     */
    public $fillingRoute;

    /**
     * @var string
     */
    public $table;

    /**
     * @var string
     */
    public $ownAttribute;

    /**
     * @var string
     */
    public $relatedModel;

    /**
     * @var string
     */
    public $relatedAttribute;

    /**
     * @var \yii\db\ActiveRecord
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_relatedList;


    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->editableAttribute) {
            throw new InvalidConfigException('$editableAttribute is required.');
        }

        if ($this->name) {
            $query = $this->getQuery();

            if (is_array($query->via)) {
                // via
                /* @var $modelClass \yii\db\ActiveRecord */
                $modelClass = $query->via[1]->modelClass;
                $this->table = $modelClass::tableName();
                $this->ownAttribute = key($query->via[1]->link);
            } else {
                // viaTable
                $this->table = $query->via->from[0];
                $this->ownAttribute = key($query->via->link);
            }

            $this->relatedModel = $query->modelClass;
            $this->relatedAttribute = reset($query->link);
        } else {
            if (!$this->table) {
                throw new InvalidConfigException('$table must be explicitly set in case of missing $name.');
            }

            if (!$this->ownAttribute) {
                throw new InvalidConfigException('$ownAttribute must be explicitly set in case of missing $name.');
            }

            if (!$this->relatedModel) {
                throw new InvalidConfigException('$relatedModel must be explicitly set in case of missing $name.');
            }

            if (!$this->relatedAttribute) {
                throw new InvalidConfigException('$relatedAttribute must be explicitly set in case of missing $name.');
            }
        }

        $this->fillingRoute = (array) $this->fillingRoute;

        parent::init();
    }

    /**
     * @param null|array $primaryKeys
     * @throws \yii\db\Exception
     */
    public function insert($primaryKeys = null)
    {
        if ($primaryKeys === null) {
            $primaryKeys = $this->getEditableList();
        }

        if (!$primaryKeys) {
            return;
        }

        $rows = [];
        foreach ($primaryKeys as $primaryKey) {
            $rows[] = [$this->_model->primaryKey, $primaryKey];
        }

        Yii::$app
            ->db
            ->createCommand()
            ->batchInsert($this->table, [$this->ownAttribute, $this->relatedAttribute], $rows)
            ->execute();
    }

    public function update()
    {
        $this->delete(array_diff($this->getRelatedList(), $this->getEditableList()));
        $this->insert(array_diff($this->getEditableList(), $this->getRelatedList()));
    }

    /**
     * @param array $primaryKeys
     * @throws \yii\db\Exception
     */
    public function delete($primaryKeys)
    {
        if (!$primaryKeys) {
            return;
        }

        Yii::$app->db->createCommand()->delete($this->table, [
            $this->ownAttribute => $this->_model->primaryKey,
            $this->relatedAttribute => $primaryKeys,
        ])->execute();
    }

    public function fill()
    {
        if (!$this->fillingRoute) {
            return;
        }

        if (in_array(Yii::$app->controller->route, $this->fillingRoute)) {
            $this->setEditableList($this->getRelatedList());
        }
    }

    /**
     * @param \yii\db\ActiveRecord $value
     */
    public function setModel($value)
    {
        $this->_model = $value;
    }

    /**
     * @return array
     */
    protected function getEditableList()
    {
        return $this->_model->{$this->editableAttribute} ?: [];
    }

    /**
     * @param array $value
     */
    protected function setEditableList($value)
    {
        $this->_model->{$this->editableAttribute} = $value;
    }

    /**
     * @return array
     */
    protected function getRelatedList()
    {
        if ($this->_relatedList) {
            return $this->_relatedList;
        }

        if ($this->name) {
            /* @var $relatedModel \yii\db\ActiveRecord */
            $relatedModel = $this->relatedModel;
            $primaryKey = $relatedModel::primaryKey()[0];

            $models = $this->_model->{$this->name};
            $primaryKeys = ArrayHelper::getColumn($models, $primaryKey);
        } else {
            $rows = (new Query)
                ->from($this->table)
                ->select($this->relatedAttribute)
                ->where([$this->ownAttribute => $this->_model->primaryKey])
                ->all();

            $primaryKeys = ArrayHelper::getColumn($rows, $this->relatedAttribute);
        }

        $this->_relatedList = $primaryKeys;

        return $primaryKeys;
    }

    /**
     * @return null|\yii\db\ActiveQuery
     */
    protected function getQuery()
    {
        if (!$this->name) {
            return null;
        }

        $methodName = 'get' . ucfirst($this->name);

        return $this->_model->$methodName();
    }
}
