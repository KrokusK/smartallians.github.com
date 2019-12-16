<?php

use yii\db\Migration;
//use yii\db\Command;

/**
 * Class m191216_044753_import_data_to_the_Response_tables
 */
class m191216_044753_import_data_to_the_Response_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // erase table records and sequences before to insert
        //$this->db->createCommand()->delete($table->user)->execute();
        //if ($table->sequenceName !== null) {
        //    $this->db->createCommand()->resetSequence($table->user, 1)->execute();
        //}
        $this->delete('{{%response}}');
        $this->delete('{{%status_response}}');

        $this->db->createCommand()->resetSequence('{{%response}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%status_response}}', 1)->execute();


        // import to the status_response table
        $this->insert('{{%status_response}}', [
            'name' => 'На рассмотрении',
        ]);
        $this->insert('{{%status_response}}', [
            'name' => 'Отклонена',
        ]);
        $this->insert('{{%status_response}}', [
            'name' => 'Принята',
        ]);


        // import to the response table
        $this->insert('{{%response}}', [
            'status_response_id' => 1,
            'request_id' => 1,
            'description' => 'Описание № 1',
            'cost' => '1 000 000 руб.',
            'period' => 1000000,
            'created_at' => 1573193110,
            'updated_at' => 1573193110
        ]);
        $this->insert('{{%response}}', [
            'status_response_id' => 1,
            'request_id' => 1,
            'description' => 'Описание № 2',
            'cost' => '2 000 000 руб.',
            'period' => 1000000,
            'created_at' => 1573193110,
            'updated_at' => 1573193110
        ]);
        $this->insert('{{%response}}', [
            'status_response_id' => 1,
            'request_id' => 3,
            'description' => 'Описание № 3',
            'cost' => '3 000 000 руб.',
            'period' => 1000000,
            'created_at' => 1573193110,
            'updated_at' => 1573193110
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // erase table records and sequences
        $this->delete('{{%response}}');
        $this->delete('{{%status_response}}');

        $this->db->createCommand()->resetSequence('{{%response}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%status_response}}', 1)->execute();
    }
}
