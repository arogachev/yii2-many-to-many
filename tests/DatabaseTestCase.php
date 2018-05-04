<?php

namespace tests;

use PHPUnit\DbUnit\DataSet\YamlDataSet;
use PHPUnit\DbUnit\TestCase;
use Yii;

abstract class DatabaseTestCase extends TestCase
{
    /**
     * @inheritdoc
     */
    public function getConnection()
    {
        return $this->createDefaultDBConnection(Yii::$app->db->pdo);
    }

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        Yii::$app->db->open();
        $sql = file_get_contents(dirname(__FILE__) . '/migrations/schema-mysql.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * @inheritdoc
     */
    protected function getDataSet()
    {
        return $this->getYamlDataSet('initial');
    }

    /**
     * Get data set from file in .yml format
     * @param string $name Data set name (file name without extension)
     * @return YamlDataSet
     */
    protected function getYamlDataSet($name)
    {
        return new YamlDataSet(dirname(__FILE__) . "/data/$name.yml");
    }
}
