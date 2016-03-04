<?php

namespace tests;

use PHPUnit_Extensions_Database_DataSet_YamlDataSet;
use PHPUnit_Extensions_Database_TestCase;
use tests\models\Test;
use Yii;

abstract class DatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
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
     * @return PHPUnit_Extensions_Database_DataSet_YamlDataSet
     */
    protected function getYamlDataSet($name)
    {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(dirname(__FILE__) . "/data/$name.yml");
    }
}
