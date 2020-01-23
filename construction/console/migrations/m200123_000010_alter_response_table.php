<?php

use yii\db\Migration;

/**
 * Class m200123_000010_alter_response_table
 */
class m200123_000010_alter_response_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // alter response table
        $this->addColumn('response', 'date_begin', $this->integer());
        $this->addColumn('response', 'date_end', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // alter response table
        $this->dropColumn('response', 'date_begin');
        $this->dropColumn('response', 'date_end');
    }
}
