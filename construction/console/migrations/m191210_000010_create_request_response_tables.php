<?php

use yii\db\Migration;

/**
 * Class m191210_000010_create_request_response_tables
 */
class m191210_000010_create_request_response_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create profile_RROD table
        $this->createTable('{{%profile_rrod}}', [
            'profile_id' => $this->integer()->notNull(),
            'request_id' => $this->integer(),
            'response_id' => $this->integer(),
            'order_id' => $this->integer(),
            'delivery_id' => $this->integer()
        ]);

        // creates index for column profile_id
        $this->createIndex(
            'idx-profile-rrod-profile-id',
            '{{%profile_rrod}}',
            'profile_id'
        );

        // creates index for column request_id
        $this->createIndex(
            'idx-profile-rrod-request-id',
            '{{%profile_rrod}}',
            'request_id'
        );

        // creates index for column response_id
        $this->createIndex(
            'idx-profile-rrod-response-id',
            '{{%profile_rrod}}',
            'response_id'
        );

        // creates index for column order_id
        $this->createIndex(
            'idx-profile-rrod-order-id',
            '{{%profile_rrod}}',
            'order_id'
        );

        // creates index for column delivery_id
        $this->createIndex(
            'idx-profile-rrod-delivery-id',
            '{{%profile_rrod}}',
            'delivery_id'
        );

        // create request table
        $this->createTable('{{%request}}', [
            'id' => $this->primaryKey(),
            'status_request_id' => $this->integer()->notNull(),
            'city_id' => $this->integer()->notNull(),
            'address' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string()->notNull(),
            'task' => $this->string()->notNull(),
            'budjet' => $this->float()->notNull(),
            'period' => $this->integer()->notNull(),
            'date_begin' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ]);

        // creates index for column status_request_id
        $this->createIndex(
            'idx-request-status-request-id',
            '{{%request}}',
            'status_request_id'
        );

        // creates index for column city_id
        $this->createIndex(
            'idx-request-city-id',
            '{{%request}}',
            'city_id'
        );

        // create status request table
        $this->createTable('{{%status_request}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create request kind job table
        $this->createTable('{{%request_kind_job}}', [
            'request_id' => $this->integer()->notNull(),
            'kind_job_id' => $this->integer()->notNull()
        ]);

        // creates index for column request_id
        $this->createIndex(
            'idx-request-kind-job-request-id',
            '{{%request_kind_job}}',
            'request_id'
        );

        // creates index for column kind_job_id
        $this->createIndex(
            'idx-request-kind-job-kind-job-id',
            '{{%request_kind_job}}',
            'kind_job_id'
        );

        // create response table
        $this->createTable('{{%response}}', [
            'id' => $this->primaryKey(),
            'status_response_id' => $this->integer()->notNull(),
            'request_id' => $this->integer()->notNull(),
            'description' => $this->string()->notNull(),
            'cost' => $this->float()->notNull(),
            'period' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ]);

        // creates index for column status_response_id
        $this->createIndex(
            'idx-response-status-response-id',
            '{{%response}}',
            'status_response_id'
        );

        // creates index for column request_id
        $this->createIndex(
            'idx-response-request-id',
            '{{%response}}',
            'request_id'
        );

        // create status response table
        $this->createTable('{{%status_response}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);


        // add foreign keys for table profile_rrod
        $this->addForeignKey(
            'fk-profile-rrod-profile-id',
            '{{%profile_rrod}}',
            'profile_id',
            '{{%profile}}',
            'id'
        );
        $this->addForeignKey(
            'fk-profile-rrod-request-id',
            '{{%profile_rrod}}',
            'request_id',
            '{{%request}}',
            'id'
        );
        $this->addForeignKey(
            'fk-profile-rrod-response-id',
            '{{%profile_rrod}}',
            'response_id',
            '{{%response}}',
            'id'
        );

        // add foreign keys for table request
        $this->addForeignKey(
            'fk-request-status-request-id',
            '{{%request}}',
            'status_request_id',
            '{{%status_request}}',
            'id'
        );
        $this->addForeignKey(
            'fk-request-city-id',
            '{{%request}}',
            'city_id',
            '{{%city}}',
            'id'
        );

        // add foreign keys for table request_kind_job
        $this->addForeignKey(
            'fk-request-kind-job-request-id',
            '{{%request_kind_job}}',
            'request_id',
            '{{%request}}',
            'id'
        );
        $this->addForeignKey(
            'fk-request-kind-job-kind-job-id',
            '{{%request_kind_job}}',
            'kind_job_id',
            '{{%kind_job}}',
            'id'
        );

        // add foreign keys for table response
        $this->addForeignKey(
            'fk-response-request-id',
            '{{%response}}',
            'request_id',
            '{{%request}}',
            'id'
        );
        $this->addForeignKey(
            'fk-response-status-response-id',
            '{{%response}}',
            'status_response_id',
            '{{%status_response}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign keys for table response
        $this->dropForeignKey(
            'fk-response-status-response-id',
            '{{%response}}'
        );
        $this->dropForeignKey(
            'fk-response-request-id',
            '{{%response}}'
        );

        // drops foreign keys for table request_kind_job
        $this->dropForeignKey(
            'fk-request-kind-job-request-id',
            '{{%request_kind_job}}'
        );
        $this->dropForeignKey(
            'fk-request-kind-job-kind-job-id',
            '{{%request_kind_job}}'
        );

        // drops foreign keys for table request
        $this->dropForeignKey(
            'fk-request-status-request-id',
            '{{%request}}'
        );
        $this->dropForeignKey(
            'fk-request-city-id',
            '{{%request}}'
        );

        // drops foreign keys for table profile_rrod
        $this->dropForeignKey(
            'fk-profile-rrod-profile-id',
            '{{%profile_rrod}}'
        );
        $this->dropForeignKey(
            'fk-profile-rrod-request-id',
            '{{%profile_rrod}}'
        );
        $this->dropForeignKey(
            'fk-profile-rrod-response-id',
            '{{%profile_rrod}}'
        );

        // drop index for column status_response_id
        $this->dropIndex(
            'idx-response-status-response-id',
            '{{%response}}'
        );

        // drop index for column request_id
        $this->dropIndex(
            'idx-response-request-id',
            '{{%response}}'
        );

        // drop index for column request_id
        $this->dropIndex(
            'idx-request-kind-job-request-id',
            '{{%request_kind_job}}'
        );

        // drop index for column kind-job-id
        $this->dropIndex(
            'idx-request-kind-job-kind-job-id',
            '{{%request_kind_job}}'
        );

        // drop index for column status-request-id
        $this->dropIndex(
            'idx-request-status-request-id',
            '{{%request}}'
        );

        // drop index for column city-id
        $this->dropIndex(
            'idx-request-city-id',
            '{{%request}}'
        );

        // drop index for column profile-id
        $this->dropIndex(
            'idx-profile-rrod-profile-id',
            '{{%profile_rrod}}'
        );

        // drop index for column request-id
        $this->dropIndex(
            'idx-profile-rrod-request-id',
            '{{%profile_rrod}}'
        );

        // drop index for column response-id
        $this->dropIndex(
            'idx-profile-rrod-response-id',
            '{{%profile_rrod}}'
        );

        // drop index for column order-id
        $this->dropIndex(
            'idx-profile-rrod-order-id',
            '{{%profile_rrod}}'
        );

        // drop index for column delivery-id
        $this->dropIndex(
            'idx-profile-rrod-delivery-id',
            '{{%profile_rrod}}'
        );

        // drop status_response table
        $this->dropTable('{{%status_response}}');

        // drop response table
        $this->dropTable('{{%response}}');

        // drop request_kind_job table
        $this->dropTable('{{%request_kind_job}}');

        // drop status_request table
        $this->dropTable('{{%status_request}}');

        // drop request table
        $this->dropTable('{{%request}}');

        // drop profile_rrod table
        $this->dropTable('{{%profile_rrod}}');
    }
}
