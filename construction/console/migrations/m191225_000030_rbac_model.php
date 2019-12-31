<?php

use yii\db\Migration;

/**
 * Class m191225_000030_rbac_model
 */
class m191225_000030_rbac_model extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create author columns
        $this->addColumn('profile', 'created_by', $this->integer());
        $this->addColumn('contractor', 'created_by', $this->integer()->notNull());
        $this->addColumn('portfolio', 'created_by', $this->integer()->notNull());
        $this->addColumn('position', 'created_by', $this->integer()->notNull());
        $this->addColumn('photo', 'created_by', $this->integer()->notNull());
        $this->addColumn('response', 'created_by', $this->integer());
        $this->addColumn('request', 'created_by', $this->integer());
        $this->addColumn('order', 'created_by', $this->integer()->notNull());
        $this->addColumn('feedback', 'created_by', $this->integer()->notNull());
        $this->addColumn('delivery', 'created_by', $this->integer()->notNull());
        $this->addColumn('materials', 'created_by', $this->integer()->notNull());
        $this->addColumn('delivery_place', 'created_by', $this->integer()->notNull());
        $this->addColumn('departure_place', 'created_by', $this->integer()->notNull());
        $this->addColumn('project', 'created_by', $this->integer()->notNull());
        $this->addColumn('job_stages', 'created_by', $this->integer()->notNull());
        $this->addColumn('project_documents', 'created_by', $this->integer()->notNull());

        // insert data in to the profile table
        $this->update('profile', ['created_by' => 1], ['id' => 1]);
        $this->update('profile', ['created_by' => 2], ['id' => 2]);
        $this->update('profile', ['created_by' => 3], ['id' => 3]);
        $this->update('profile', ['created_by' => 4], ['id' => 4]);
        $this->update('profile', ['created_by' => 5], ['id' => 5]);
        $this->alterColumn('profile', 'created_by', $this->integer()->notNull());

        // insert data in to the request table
        $this->update('request', ['created_by' => 1], ['id' => 1]);
        $this->update('request', ['created_by' => 1], ['id' => 2]);
        $this->update('request', ['created_by' => 2], ['id' => 3]);
        $this->alterColumn('request', 'created_by', $this->integer()->notNull());

        // insert data in to the response table
        $this->update('response', ['created_by' => 3], ['id' => 1]);
        $this->update('response', ['created_by' => 3], ['id' => 2]);
        $this->update('response', ['created_by' => 4], ['id' => 3]);
        $this->alterColumn('response', 'created_by', $this->integer()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop author columns
        $this->dropColumn('profile', 'created_by');
        $this->dropColumn('contractor', 'created_by');
        $this->dropColumn('portfolio', 'created_by');
        $this->dropColumn('position', 'created_by');
        $this->dropColumn('photo', 'created_by');
        $this->dropColumn('response', 'created_by');
        $this->dropColumn('request', 'created_by');
        $this->dropColumn('order', 'created_by');
        $this->dropColumn('delivery', 'created_by');
        $this->dropColumn('materials', 'created_by');
        $this->dropColumn('delivery_place', 'created_by');
        $this->dropColumn('departure_place', 'created_by');
        $this->dropColumn('project', 'created_by');
        $this->dropColumn('job_stages', 'created_by');
        $this->dropColumn('project_documents', 'created_by');
    }
}
