<?php

use yii\db\Migration;
//use yii\db\Command;

/**
 * Class m191215_044753_import_data_to_the_Request_tables
 */
class m191215_044753_import_data_to_the_Request_tables extends Migration
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
        $this->delete('{{%request}}');
        $this->delete('{{%status_request}}');

        $this->db->createCommand()->resetSequence('{{%request}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%status_request}}', 1)->execute();


        // import to the status_request table
        $this->insert('{{%status_request}}', [
            'name' => 'На рассмотрении',
        ]);
        $this->insert('{{%status_request}}', [
            'name' => 'Отклонена',
        ]);
        $this->insert('{{%status_request}}', [
            'name' => 'Принята',
        ]);


        // import to the request table
        $this->insert('{{%request}}', [
            'status_request_id' => 1,
            'city_id' => 1,
            'address' => 'ул. Пушкина, д. 11',
            'name' => 'Заявка № 1',
            'description' => 'Описание',
            'task' => 'Техническое задание',
            'budjet' => 1000000,
            'period' => 1000000,
            'date_begin' => 1573193110,
            'date_end' => 1573203110,
            'created_at' => 1573193110,
            'updated_at' => 1573193110
        ]);
        $this->insert('{{%request}}', [
            'status_request_id' => 2,
            'city_id' => 2,
            'address' => 'ул. Ленина, д. 15',
            'name' => 'Заявка № 2',
            'description' => 'Описание',
            'task' => 'Техническое задание',
            'budjet' => 2000000,
            'period' => 1000000,
            'date_begin' => 1573193110,
            'date_end' => 1573203110,
            'created_at' => 1573193110,
            'updated_at' => 1573193110
        ]);
        $this->insert('{{%request}}', [
            'status_request_id' => 3,
            'city_id' => 1,
            'address' => 'ул. С. Лазо, д. 20',
            'name' => 'Заявка № 3',
            'description' => 'Описание',
            'task' => 'Техническое задание',
            'budjet' => 3000000,
            'period' => 1000000,
            'date_begin' => 1573193110,
            'date_end' => 1573203110,
            'created_at' => 1573193110,
            'updated_at' => 1573193110
        ]);

        // import to the profile_rrod table
        $this->insert('{{%profile_rrod}}', [
            'profile_id' => 1,
            'request_id' => 1
        ]);
        $this->insert('{{%profile_rrod}}', [
            'profile_id' => 1,
            'request_id' => 2
        ]);
        $this->insert('{{%profile_rrod}}', [
            'profile_id' => 1,
            'request_id' => 3
        ]);

        // import to the kind_job table
        $this->insert('{{%kind_job}}', [
            'name' => 'Укладка напольной плитки'
        ]);
        $this->insert('{{%kind_job}}', [
            'name' => 'Укладка ламината'
        ]);
        $this->insert('{{%kind_job}}', [
            'name' => 'Поклейка обоев'
        ]);
        $this->insert('{{%kind_job}}', [
            'name' => 'Покраска'
        ]);
        $this->insert('{{%kind_job}}', [
            'name' => 'Установка окон'
        ]);

        // import to the request_kind_job table
        /*$this->insert('{{%request_kind_job}}', [
            'kind_job_id' => 1,
            'request_id' => 1
        ]);
        $this->insert('{{%request_kind_job}}', [
            'kind_job_id' => 5,
            'request_id' => 2
        ]);
        $this->insert('{{%request_kind_job}}', [
            'kind_job_id' => 3,
            'request_id' => 3
        ]);*/
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // erase table records and sequences
        $this->delete('{{%request}}');
        $this->delete('{{%status_request}}');

        $this->db->createCommand()->resetSequence('{{%request}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%status_request}}', 1)->execute();
    }
}
