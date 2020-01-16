<?php

use yii\db\Migration;

/**
 * Class m200116_000010_alter_request_table
 */
class m200116_000010_alter_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // alter request table
        $this->alterColumn('request', 'description', $this->string(512));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        
    }
}
