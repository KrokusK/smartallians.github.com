<?php

use yii\db\Migration;

/**
 * Class m200116_000015_alter_materials_table
 */
class m200116_000015_alter_materials_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // alter materials table
        $this->addColumn('materials', 'measure', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // alter materials table
        $this->dropColumn('materials', 'measure');
    }
}
