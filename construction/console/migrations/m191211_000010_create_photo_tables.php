<?php

use yii\db\Migration;

/**
 * Class m191211_000010_create_photo_tables
 */
class m191211_000010_create_photo_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create photo table
        $this->createTable('{{%photo}}', [
            'id' => $this->primaryKey(),
            'request_id' => $this->integer(),
            'response_id' => $this->integer(),
            'position_id' => $this->integer(),
            'caption' => $this->string(),
            'description' => $this->string(),
            'path' => $this->string()
        ]);

        // creates index for column request_id
        $this->createIndex(
            'idx-photo-request-id',
            '{{%photo}}',
            'request_id'
        );

        // creates index for column response_id
        $this->createIndex(
            'idx-photo-response-id',
            '{{%photo}}',
            'response_id'
        );

        // creates index for column position_id
        $this->createIndex(
            'idx-photo-position-id',
            '{{%photo}}',
            'position_id'
        );

        // add foreign keys for table photo
        $this->addForeignKey(
            'fk-photo-request-id',
            '{{%photo}}',
            'request_id',
            '{{%request}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-photo-response-id',
            '{{%photo}}',
            'response_id',
            '{{%response}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-photo-position-id',
            '{{%photo}}',
            'position_id',
            '{{%position}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign keys for table photo
        $this->dropForeignKey(
            'fk-photo-position-id',
            '{{%photo}}'
        );
        $this->dropForeignKey(
            'fk-photo-response-id',
            '{{%photo}}'
        );
        $this->dropForeignKey(
            'fk-photo-request-id',
            '{{%photo}}'
        );

        // drop index for column request_id
        $this->dropIndex(
            'idx-photo-request-id',
            '{{%photo}}'
        );

        // drop index for column response_id
        $this->dropIndex(
            'idx-photo-response-id',
            '{{%photo}}'
        );

        // drop index for column position_id
        $this->dropIndex(
            'idx-photo-position-id',
            '{{%photo}}'
        );

        // drop photo table
        $this->dropTable('{{%photo}}');
    }
}
