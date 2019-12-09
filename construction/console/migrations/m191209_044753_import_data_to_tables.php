<?php

use yii\db\Migration;
//use yii\db\Command;

/**
 * Class m191209_044753_import_data_to_tables
 */
class m191209_044753_import_data_to_tables extends Migration
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
        $this->delete('{{%specialization}}');
        $this->delete('{{%profile_specialization}}');
        $this->delete('{{%region}}');
        $this->delete('{{%city}}');
        $this->delete('{{%profile_city}}');
        $this->delete('{{%kind_user}}');
        $this->delete('{{%profile}}');
        $this->delete('{{%user}}');

        $this->db->createCommand()->resetSequence('{{%specialization}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%region}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%city}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%kind_user}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%profile}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%user}}', 1)->execute();


        // import to the user table
        $this->insert('{{%user}}', [
            'username' => 'user1',
            'auth_key' => 'NEzqJuqXo8wioifO2fvqlJx5USw00JJX',
            'password_hash' => '$2y$13$spHblbtmjJ3z9m1bMG37sOL32o9JHACU/zMAGYBjz2Z2DT47jd/u2', //password 123456
            'password_reset_token' => null,
            'email' => 'user2@test.test',
            'status' => 10,
            'created_at' => 1573193110,
            'updated_at' => 1573193110,
            'verification_token' => 'qrc1yffNDZ8y4mhwhxYDfn7seOLDZumT_1573193110'
        ]);

        $this->insert('{{%user}}', [
            'username' => 'user2',
            'auth_key' => 'GrPhvOC9giYGShYQxw_qjPBTtoXlrblI',
            'password_hash' => '$2y$13$bhWAaZFgXYvlvNio51hwx.7FyrefJZpn3AL.oB.0OGCW8.Up22jze', //password 123456
            'password_reset_token' => null,
            'email' => 'user2@test.test',
            'status' => 10,
            'created_at' => 1573193147,
            'updated_at' => 1573193147,
            'verification_token' => 'mkmthVMPWftYDhW_fiyWKLQ681CJMbZy_1573193147'
        ]);

        $this->insert('{{%user}}', [
            'username' => 'user3',
            'auth_key' => 'I4TjFLnnewp4R0YUJrbKZSUO-Dcb93Ru',
            'password_hash' => '$2y$13$EmF3VdlySgWBnTsziNl98uOB/BVK/0TOk2Vio2NSNSiS1yqpI2G9O', //password 123456
            'password_reset_token' => null,
            'email' => 'user3@test.test',
            'status' => 10,
            'created_at' => 1575869733,
            'updated_at' => 1575869733,
            'verification_token' => 'm4OENqU6LSu-tucLLb2xZq18TKMuXfNH_1575869733'
        ]);

        $this->insert('{{%user}}', [
            'username' => 'user4',
            'auth_key' => 'rpq46idfeDAhEFFqviOBRKwfIg5a2VkC',
            'password_hash' => '$2y$13$rOYhOjjH.72lMnROTjuOr.1EKhXDvFdzSfS2LEcGjrMW0jzoZzo5.', //password 123456
            'password_reset_token' => null,
            'email' => 'user4@test.test',
            'status' => 10,
            'created_at' => 1575869895,
            'updated_at' => 1575869895,
            'verification_token' => '3dc9r--06f7lUdLjD1hpk3iXyeclb13R_1575869895'
        ]);


        // import to the region table
        $this->insert('{{%region}}', [
            'name' => 'Томская область'
        ]);


        // import to the city table
        $this->insert('{{%city}}', [
            'name' => 'Томск',
            'region_id' => 1
        ]);
        $this->insert('{{%city}}', [
            'name' => 'Северск',
            'region_id' => 1
        ]);


        // import to the specialization table
        $this->insert('{{%specialization}}', [
            'name' => 'Специализация вариант 1'
        ]);
        $this->insert('{{%specialization}}', [
            'name' => 'Специализация вариант 2'
        ]);


        // import to the kind user table
        $this->insert('{{%kind_user}}', [
            'name' => 'Заказчик'
        ]);
        $this->insert('{{%kind_user}}', [
            'name' => 'Исполнитель'
        ]);
        $this->insert('{{%kind_user}}', [
            'name' => 'Посредник'
        ]);
        $this->insert('{{%kind_user}}', [
            'name' => 'Поставщик'
        ]);
        $this->insert('{{%kind_user}}', [
            'name' => 'Консультант'
        ]);


        // import to the profile table
        $this->insert('{{%profile}}', [
            'user_id' => 1,
            'kind_user_id' => 1,
            'fio' => 'Иванов Иван Иванович',
            'firm_name' => 'Фирма вариант 1',
            'inn' => '123456789012',
            'site' => 'https://www.tomsk.ru/',
            'avatar' => 'https://download.hdd.tomsk.ru/preview/ftnfjyrg.jpg',
            'created_at' => 1575869895,
            'updated_at' => 1575869895
        ]);
        $this->insert('{{%profile}}', [
            'user_id' => 2,
            'kind_user_id' => 2,
            'fio' => 'Петров Петр Петрович',
            'firm_name' => 'Фирма вариант 2',
            'inn' => '123456789012',
            'site' => 'https://www.tomsk.ru/',
            'avatar' => 'https://download.hdd.tomsk.ru/preview/ftnfjyrg.jpg',
            'created_at' => 1575869895,
            'updated_at' => 1575869895
        ]);
        $this->insert('{{%profile}}', [
            'user_id' => 3,
            'kind_user_id' => 3,
            'fio' => 'Сидоров Сидр Сидорович',
            'firm_name' => 'Фирма вариант 3',
            'inn' => '123456789012',
            'site' => 'https://www.tomsk.ru/',
            'avatar' => 'https://download.hdd.tomsk.ru/preview/ftnfjyrg.jpg',
            'created_at' => 1575869895,
            'updated_at' => 1575869895
        ]);
        $this->insert('{{%profile}}', [
            'user_id' => 4,
            'kind_user_id' => 4,
            'fio' => 'Путин Владимир Владимирович',
            'firm_name' => 'Фирма вариант 4',
            'inn' => '123456789012',
            'site' => 'https://www.tomsk.ru/',
            'avatar' => 'https://download.hdd.tomsk.ru/preview/ftnfjyrg.jpg',
            'created_at' => 1575869895,
            'updated_at' => 1575869895
        ]);
        $this->insert('{{%profile}}', [
            'user_id' => 5,
            'kind_user_id' => 5,
            'fio' => 'Медведев Дмитрий Анатольевич',
            'firm_name' => 'Фирма вариант 5',
            'inn' => '123456789012',
            'site' => 'https://www.tomsk.ru/',
            'avatar' => 'https://download.hdd.tomsk.ru/preview/ftnfjyrg.jpg',
            'created_at' => 1575869895,
            'updated_at' => 1575869895
        ]);


        // import to the profile_specialization table
        $this->insert('{{%profile_specialization}}', [
            'profile_id' => 1,
            'specialization_id' => 1
        ]);
        $this->insert('{{%profile_specialization}}', [
            'profile_id' => 2,
            'specialization_id' => 1
        ]);
        $this->insert('{{%profile_specialization}}', [
            'profile_id' => 2,
            'specialization_id' => 2
        ]);


        // import to the profile_city table
        $this->insert('{{%profile_city}}', [
            'profile_id' => 1,
            'city_id' => 1
        ]);
        $this->insert('{{%profile_city}}', [
            'profile_id' => 2,
            'city_id' => 1
        ]);
        $this->insert('{{%profile_city}}', [
            'profile_id' => 2,
            'city_id' => 2
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // erase table records and sequences
        $this->delete('{{%specialization}}');
        $this->delete('{{%profile_specialization}}');
        $this->delete('{{%region}}');
        $this->delete('{{%city}}');
        $this->delete('{{%profile_city}}');
        $this->delete('{{%kind_user}}');
        $this->delete('{{%profile}}');
        $this->delete('{{%user}}');

        $this->db->createCommand()->resetSequence('{{%specialization}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%region}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%city}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%kind_user}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%profile}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%user}}', 1)->execute();
    }
}
