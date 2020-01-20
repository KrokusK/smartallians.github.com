<?php

use yii\db\Migration;

/**
 * Class m200116_000010_alter_request_table
 */
class m200120_000010_alter_response_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // alter request table
        $this->alterColumn('response', 'status_response_id', $this->integer());
        $this->alterColumn('response', 'cost', $this->float());
        $this->alterColumn('response', 'period', $this->integer());
        $this->alterColumn('response', 'description', $this->string(512));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        
    }
}
