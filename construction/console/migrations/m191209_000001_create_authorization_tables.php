<?php

use yii\db\Migration;

/**
 * Class m191209_000001_create_authorization_tables
 */
class m191209_000001_create_authorization_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create profile table
        $this->createTable('{{%profile}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'kind_user_id' => $this->integer()->notNull(),
            'type_job_id' => $this->integer()->notNull(),
            'fio' => $this->string(),
            'firm_name' => $this->string(),
            'inn' => $this->string(),
            'site' => $this->string(),
            'avatar' => $this->string(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ]);

        // creates index for column user_id
        $this->createIndex(
            'idx-profile-user-id',
            '{{%profile}}',
            'user_id'
        );

        // creates index for column kind_user_id
        $this->createIndex(
            'idx-profile-kind-user-id',
            '{{%profile}}',
            'kind_user_id'
        );

        // create kind user table
        $this->createTable('{{%kind_user}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create type job table
        $this->createTable('{{%type_job}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create profile city table
        $this->createTable('{{%profile_city}}', [
            'profile_id' => $this->integer()->notNull(),
            'city_id' => $this->integer()->notNull()
        ]);

        // creates index for column profile_id
        $this->createIndex(
            'idx-profile-city-profile-id',
            '{{%profile_city}}',
            'profile_id'
        );

        // creates index for column city_id
        $this->createIndex(
            'idx-profile-city-city-id',
            '{{%profile_city}}',
            'city_id'
        );

        // create city table
        $this->createTable('{{%city}}', [
            'id' => $this->primaryKey(),
            'region_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        // creates index for column region_id
        $this->createIndex(
            'idx-city-region-id',
            '{{%city}}',
            'region_id'
        );

        // create region table
        $this->createTable('{{%region}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create profile specialization table
        $this->createTable('{{%profile_specialization}}', [
            'profile_id' => $this->integer()->notNull(),
            'specialization_id' => $this->integer()->notNull()
        ]);

        // creates index for column profile_id
        $this->createIndex(
            'idx-profile-specialization-profile-id',
            '{{%profile_specialization}}',
            'profile_id'
        );

        // creates index for column specialization_id
        $this->createIndex(
            'idx-profile-specialization-specialization-id',
            '{{%profile_specialization}}',
            'specialization_id'
        );

        // create specialization table
        $this->createTable('{{%specialization}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // add foreign key for table profile
        $this->addForeignKey(
            'fk-profile-user-id',
            '{{%profile}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // add foreign key for table profile
        $this->addForeignKey(
            'fk-profile-kind-user-id',
            '{{%profile}}',
            'kind_user_id',
            '{{%kind_user}}',
            'id'
        );

        // add foreign key for table profile
        $this->addForeignKey(
            'fk-profile-type-job-id',
            '{{%profile}}',
            'type_job_id',
            '{{%type_job}}',
            'id'
        );

        // add foreign key for table profile_city
        $this->addForeignKey(
            'fk-profile-city-profile-id',
            '{{%profile_city}}',
            'profile_id',
            '{{%profile}}',
            'id',
            'CASCADE'
        );

        // add foreign key for table profile_city
        $this->addForeignKey(
            'fk-profile-city-city-id',
            '{{%profile_city}}',
            'city_id',
            '{{%city}}',
            'id'
        );

        // add foreign key for table city
        $this->addForeignKey(
            'fk-city-region-id',
            '{{%city}}',
            'region_id',
            '{{%region}}',
            'id'
        );

        // add foreign key for table profile_specialization
        $this->addForeignKey(
            'fk-profile-specialization-profile-id',
            '{{%profile_specialization}}',
            'profile_id',
            '{{%profile}}',
            'id',
            'CASCADE'
        );

        // add foreign key for table profile_specialization
        $this->addForeignKey(
            'fk-profile-specialization-specialization-id',
            '{{%profile_specialization}}',
            'specialization_id',
            '{{%specialization}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table profile_specialization
        $this->dropForeignKey(
            'fk-profile-specialization-specialization-id',
            '{{%profile_specialization}}'
        );

        // drops foreign key for table profile_specialization
        $this->dropForeignKey(
            'fk-profile-specialization-profile-id',
            '{{%profile_specialization}}'
        );

        // drop foreign key for table city
        $this->dropForeignKey(
            'fk-city-region-id',
            '{{%city}}'
        );

        // drop foreign key for table profile_city
        $this->dropForeignKey(
            'fk-profile-city-city-id',
            '{{%profile_city}}'
        );

        // drop foreign key for table profile_city
        $this->dropForeignKey(
            'fk-profile-city-profile-id',
            '{{%profile_city}}'
        );

        // drop foreign key for table profile
        $this->dropForeignKey(
            'fk-profile-type-job-id',
            '{{%profile}}'
        );

        // drop foreign key for table profile
        $this->dropForeignKey(
            'fk-profile-kind-user-id',
            '{{%profile}}'
        );

        // drop foreign key for table profile
        $this->dropForeignKey(
            'fk-profile-user-id',
            '{{%profile}}'
        );

        // drop specialization table
        $this->dropTable('{{%specialization}}');

        // drop index for column specialization_id
        $this->dropIndex(
            'idx-profile-specialization-specialization-id',
            '{{%profile_specialization}}'
        );

        // drop index for column profile_id
        $this->dropIndex(
            'idx-profile-specialization-profile-id',
            '{{%profile_specialization}}'
        );

        // drop profile specialization table
        $this->dropTable('{{%profile_specialization}}');

        // drop region table
        $this->dropTable('{{%region}}');

        // drop index for column region_id
        $this->dropIndex(
            'idx-city-region-id',
            '{{%city}}'
        );

        // drop city table
        $this->dropTable('{{%city}}');

        // drop index for column city_id
        $this->dropIndex(
            'idx-profile-city-city-id',
            '{{%profile_city}}'
        );

        // drop index for column profile_id
        $this->dropIndex(
            'idx-profile-city-profile-id',
            '{{%profile_city}}'
        );

        // drop profile city table
        $this->dropTable('{{%profile_city}}');

        // drop type job table
        $this->dropTable('{{%type_job}}');

        // drop kind user table
        $this->dropTable('{{%kind_user}}');

        // drop index for column kind_user_id
        $this->dropIndex(
            'idx-profile-kind-user-id',
            '{{%profile}}'
        );

        // drop index for column user_id
        $this->dropIndex(
            'idx-profile-user-id',
            '{{%profile}}'
        );

        // drop profile table
        $this->dropTable('{{%profile}}');
    }
}
