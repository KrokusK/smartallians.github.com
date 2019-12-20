<?php

use yii\db\Migration;

/**
 * Class m191220_000025_create_delivery_tables
 */
class m191220_000025_create_delivery_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create delivery_place table
        $this->createTable('{{%delivery_place}}', [
            'id' => $this->primaryKey(),
            'city_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        // creates index for column city_id
        $this->createIndex(
            'idx-delivery-place-city-id',
            '{{%delivery_place}}',
            'city_id'
        );

        // create departure_place table
        $this->createTable('{{%departure_place}}', [
            'id' => $this->primaryKey(),
            'city_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        // creates index for column city_id
        $this->createIndex(
            'idx-departure-place-city-id',
            '{{%departure_place}}',
            'city_id'
        );

        // create status_delivery table
        $this->createTable('{{%status_delivery}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create delivery table
        $this->createTable('{{%delivery}}', [
            'id' => $this->primaryKey(),
            'delivery_place_id' => $this->integer()->notNull(),
            'departure_place_id' => $this->integer()->notNull(),
            'status_payment_id' => $this->integer()->notNull(),
            'status_delivery_id' => $this->integer()->notNull(),
            'cost' => $this->float()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()
        ]);

        // creates index for column delivery_place_id
        $this->createIndex(
            'idx-delivery-delivery-place-id',
            '{{%delivery}}',
            'delivery_place_id'
        );

        // creates index for column departure_place_id
        $this->createIndex(
            'idx-delivery-departure-place-id',
            '{{%delivery}}',
            'departure_place_id'
        );

        // creates index for column status_delivery_id
        $this->createIndex(
            'idx-delivery-status-delivery-id',
            '{{%delivery}}',
            'status_delivery_id'
        );

        // creates index for column status_payment_id
        $this->createIndex(
            'idx-delivery-status-payment-id',
            '{{%delivery}}',
            'status_payment_id'
        );

        // add foreign keys for table delivery_place
        $this->addForeignKey(
            'fk-delivery-place-city-id',
            '{{%delivery_place}}',
            'city_id',
            '{{%city}}',
            'id'
        );

        // add foreign keys for table departure_place
        $this->addForeignKey(
            'fk-departure-place-city-id',
            '{{%departure_place}}',
            'city_id',
            '{{%city}}',
            'id'
        );

        // add foreign keys for table delivery
        $this->addForeignKey(
            'fk-delivery-delivery-place-id',
            '{{%delivery}}',
            'delivery_place_id',
            '{{%delivery_place}}',
            'id'
        );
        $this->addForeignKey(
            'fk-delivery-departure-place-id',
            '{{%delivery}}',
            'departure_place_id',
            '{{%departure_place}}',
            'id'
        );
        $this->addForeignKey(
            'fk-delivery-status-delivery-id',
            '{{%delivery}}',
            'status_delivery_id',
            '{{%status_delivery}}',
            'id'
        );
        $this->addForeignKey(
            'fk-delivery-status-payment-id',
            '{{%delivery}}',
            'status_payment_id',
            '{{%status_payment}}',
            'id'
        );

        // add foreign keys for table profile_rrod
        $this->addForeignKey(
            'fk-profile-rrod-delivery-id',
            '{{%profile_rrod}}',
            'delivery_id',
            '{{%delivery}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign keys for table delivery
        $this->dropForeignKey(
            'fk-delivery-status-payment-id',
            '{{%delivery}}'
        );
        $this->dropForeignKey(
            'fk-delivery-status-delivery-id',
            '{{%delivery}}'
        );
        $this->dropForeignKey(
            'fk-delivery-departure-place-id',
            '{{%delivery}}'
        );
        $this->dropForeignKey(
            'fk-delivery-delivery-place-id',
            '{{%delivery}}'
        );

        // drops foreign key for table departure_place
        $this->dropForeignKey(
            'fk-departure-place-city-id',
            '{{%departure_place}}'
        );

        // drops foreign key for table delivery_place
        $this->dropForeignKey(
            'fk-delivery-place-city-id',
            '{{%delivery_place}}'
        );

        // drops foreign key for table profile_rrod
        $this->dropForeignKey(
            'fk-profile-rrod-delivery-id',
            '{{%profile_rrod}}'
        );

        // drop index for column status_payment_id
        $this->dropIndex(
            'idx-delivery-status-payment-id',
            '{{%delivery}}'
        );

        // drop index for column status_delivery_id
        $this->dropIndex(
            'idx-delivery-status-delivery-id',
            '{{%delivery}}'
        );

        // drop index for column departure_place_id
        $this->dropIndex(
            'idx-delivery-departure-place-id',
            '{{%delivery}}'
        );

        // drop index for column delivery_place_id
        $this->dropIndex(
            'idx-delivery-delivery-place-id',
            '{{%delivery}}'
        );

        // drop status_payment table
        $this->dropTable('{{%status_delivery}}');

        // drop status_complection table
        $this->dropTable('{{%delivery_place}}');

        // drop status_complection table
        $this->dropTable('{{%departure_place}}');

        // drop delivery table
        $this->dropTable('{{%delivery}}');
    }
}
