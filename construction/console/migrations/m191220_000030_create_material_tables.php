<?php

use yii\db\Migration;

/**
 * Class m191220_000030_create_delivery_tables
 */
class m191220_000030_create_material_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create material_type table
        $this->createTable('{{%material_type}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create status_material table
        $this->createTable('{{%status_material}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create materials table
        $this->createTable('{{%materials}}', [
            'id' => $this->primaryKey(),
            'request_id' => $this->integer(),
            'delivery_id' => $this->integer(),
            'material_type_id' => $this->integer()->notNull(),
            'status_material_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'cost' => $this->float()->notNull(),
            'count' => $this->integer()->notNull()
        ]);

        // creates index for column request_id
        $this->createIndex(
            'idx-materials-request-id',
            '{{%materials}}',
            'request_id'
        );

        // creates index for column delivery_id
        $this->createIndex(
            'idx-materials-delivery-id',
            '{{%materials}}',
            'delivery_id'
        );

        // creates index for column material_type_id
        $this->createIndex(
            'idx-materials-material-type-id',
            '{{%materials}}',
            'material_type_id'
        );

        // creates index for column status_material_id
        $this->createIndex(
            'idx-materials-status-material-id',
            '{{%materials}}',
            'status_material_id'
        );

        // add foreign keys for table materials
        $this->addForeignKey(
            'fk-materials-request-id',
            '{{%materials}}',
            'request_id',
            '{{%request}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-materials-delivery-id',
            '{{%materials}}',
            'delivery_id',
            '{{%delivery}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-materials-material-type-id',
            '{{%materials}}',
            'material_type_id',
            '{{%material_type}}',
            'id'
        );
        $this->addForeignKey(
            'fk-materials-status-material-id',
            '{{%materials}}',
            'status_material_id',
            '{{%status_material}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign keys for table materials
        $this->dropForeignKey(
            'fk-materials-status-material-id',
            '{{%materials}}'
        );
        $this->dropForeignKey(
            'fk-materials-material-type-id',
            '{{%materials}}'
        );
        $this->dropForeignKey(
            'fk-materials-delivery-id',
            '{{%materials}}'
        );
        $this->dropForeignKey(
            'fk-materials-request-id',
            '{{%materials}}'
        );

        // drop index for column status_material_id
        $this->dropIndex(
            'idx-materials-status-material-id',
            '{{%materials}}'
        );

        // drop index for column material_type_id
        $this->dropIndex(
            'idx-materials-material-type-id',
            '{{%materials}}'
        );

        // drop index for column delivery_id
        $this->dropIndex(
            'idx-materials-delivery-id',
            '{{%materials}}'
        );

        // drop index for column request_id
        $this->dropIndex(
            'idx-materials-request-id',
            '{{%materials}}'
        );

        // drop materials table
        $this->dropTable('{{%materials}}');

        // drop status_material table
        $this->dropTable('{{%status_material}}');

        // drop material_type table
        $this->dropTable('{{%material_type}}');
    }
}
