<?php

use yii\db\Migration;

/**
 * Class m191220_000020_create_order_tables
 */
class m191220_000020_create_order_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create status_complection table
        $this->createTable('{{%status_complection}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create status_payment table
        $this->createTable('{{%status_payment}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create order table
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'request_id' => $this->integer()->notNull(),
            'response_id' => $this->integer()->notNull(),
            'status_payment_id' => $this->integer()->notNull(),
            'status_complection_id' => $this->integer()->notNull(),
            'project_id' => $this->integer()->notNull(),
            'feedback_id' => $this->integer()->notNull()
        ]);

        // creates index for column request_id
        $this->createIndex(
            'idx-order-request-id',
            '{{%order}}',
            'request_id'
        );

        // creates index for column response_id
        $this->createIndex(
            'idx-order-response-id',
            '{{%order}}',
            'response_id'
        );

        // creates index for column status_payment_id
        $this->createIndex(
            'idx-order-status-payment-id',
            '{{%order}}',
            'status_payment_id'
        );

        // creates index for column status_complection_id
        $this->createIndex(
            'idx-order-status-complection-id',
            '{{%order}}',
            'status_complection_id'
        );

        // creates index for column project_id
        $this->createIndex(
            'idx-order-project-id',
            '{{%order}}',
            'project_id'
        );

        // creates index for column feedback_id
        $this->createIndex(
            'idx-order-feedback-id',
            '{{%order}}',
            'feedback_id'
        );

        // add foreign keys for table order
        $this->addForeignKey(
            'fk-order-request-id',
            '{{%order}}',
            'request_id',
            '{{%request}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-order-response-id',
            '{{%order}}',
            'response_id',
            '{{%response}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-order-status-payment-id',
            '{{%order}}',
            'status_payment_id',
            '{{%status_payment}}',
            'id'
        );
        $this->addForeignKey(
            'fk-order-status-complection-id',
            '{{%order}}',
            'status_complection_id',
            '{{%status_complection}}',
            'id'
        );
        $this->addForeignKey(
            'fk-order-project-id',
            '{{%order}}',
            'project_id',
            '{{%project}}',
            'id'
        );
        $this->addForeignKey(
            'fk-order-feedback-id',
            '{{%order}}',
            'feedback_id',
            '{{%feedback}}',
            'id'
        );

        // add foreign keys for table profile_rrod
        $this->addForeignKey(
            'fk-profile-rrod-order-id',
            '{{%profile_rrod}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign keys for table order
        $this->dropForeignKey(
            'fk-order-feedback-id',
            '{{%order}}'
        );
        $this->dropForeignKey(
            'fk-order-project-id',
            '{{%order}}'
        );
        $this->dropForeignKey(
            'fk-order-status-complection-id',
            '{{%order}}'
        );
        $this->dropForeignKey(
            'fk-order-status-payment-id',
            '{{%order}}'
        );
        $this->dropForeignKey(
            'fk-order-response-id',
            '{{%order}}'
        );
        $this->dropForeignKey(
            'fk-order-request-id',
            '{{%order}}'
        );

        // drops foreign key for table profile_rrod
        $this->dropForeignKey(
            'fk-profile-rrod-order-id',
            '{{%profile_rrod}}'
        );

        // drop index for column feedback_id
        $this->dropIndex(
            'idx-order-feedback-id',
            '{{%order}}'
        );

        // drop index for column project_id
        $this->dropIndex(
            'idx-order-project-id',
            '{{%order}}'
        );

        // drop index for column status_complection_id
        $this->dropIndex(
            'idx-order-status-complection-id',
            '{{%order}}'
        );

        // drop index for column status_payment_id
        $this->dropIndex(
            'idx-order-status-payment-id',
            '{{%order}}'
        );

        // drop index for column response_id
        $this->dropIndex(
            'idx-order-response-id',
            '{{%order}}'
        );

        // drop index for column request_id
        $this->dropIndex(
            'idx-order-request-id',
            '{{%order}}'
        );

        // drop order table
        $this->dropTable('{{%order}}');

        // drop status_payment table
        $this->dropTable('{{%status_payment}}');

        // drop status_complection table
        $this->dropTable('{{%status_complection}}');
    }
}
