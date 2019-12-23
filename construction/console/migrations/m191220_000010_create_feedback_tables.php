<?php

use yii\db\Migration;

/**
 * Class m191220_000010_create_feedback_tables
 */
class m191220_000010_create_feedback_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create status_feedback table
        $this->createTable('{{%status_feedback}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create feedback table
        $this->createTable('{{%feedback}}', [
            'id' => $this->primaryKey(),
            'profile_id' => $this->integer()->notNull(),
            'status_feedback_id' => $this->integer()->notNull(),
            'content' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ]);

        // creates index for column profile_id
        $this->createIndex(
            'idx-feedback-profile-id',
            '{{%feedback}}',
            'profile_id'
        );

        // creates index for column status_feedback_id
        $this->createIndex(
            'idx-feedback-status-feedback-id',
            '{{%feedback}}',
            'status_feedback_id'
        );

        // add foreign keys for table feedback
        $this->addForeignKey(
            'fk-feedback-profile-id',
            '{{%feedback}}',
            'profile_id',
            '{{%profile}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-feedback-status-feedback-id',
            '{{%feedback}}',
            'status_feedback_id',
            '{{%status_feedback}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign keys for table photo
        $this->dropForeignKey(
            'fk-feedback-status-feedback-id',
            '{{%feedback}}'
        );
        $this->dropForeignKey(
            'fk-feedback-profile-id',
            '{{%feedback}}'
        );

        // drop index for column status_feedback_id
        $this->dropIndex(
            'idx-feedback-status-feedback-id',
            '{{%feedback}}'
        );

        // drop index for column profile_id
        $this->dropIndex(
            'idx-feedback-profile-id',
            '{{%feedback}}'
        );

        // drop feedback table
        $this->dropTable('{{%feedback}}');

        // drop status_feedback table
        $this->dropTable('{{%status_feedback}}');
    }
}
