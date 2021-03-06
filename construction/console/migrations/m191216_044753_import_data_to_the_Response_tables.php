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
            'cost' => 1000000,
            'period' => 1000000,
            'created_at' => 1573193110,
            'updated_at' => 1573193110
        ]);
        $this->insert('{{%response}}', [
            'status_response_id' => 1,
            'request_id' => 1,
            'description' => 'Описание № 2',
            'cost' => 2000000,
            'period' => 1000000,
            'created_at' => 1573193110,
            'updated_at' => 1573193110
        ]);
        $this->insert('{{%response}}', [
            'status_response_id' => 1,
            'request_id' => 3,
            'description' => 'Описание № 3',
            'cost' => 3000000,
            'period' => 1000000,
            'created_at' => 1573193110,
            'updated_at' => 1573193110
        ]);

        // import to the profile_rrod table
        $this->insert('{{%profile_rrod}}', [
            'profile_id' => 3,
            'response_id' => 1
        ]);
        $this->insert('{{%profile_rrod}}', [
            'profile_id' => 3,
            'response_id' => 2
        ]);
        $this->insert('{{%profile_rrod}}', [
            'profile_id' => 4,
            'response_id' => 3
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
