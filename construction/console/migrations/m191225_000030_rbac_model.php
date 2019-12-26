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
        $this->addColumn('profile', 'createdby', $this->integer());
        $this->addColumn('contractor', 'createdby', $this->integer());
        $this->addColumn('portfolio', 'createdby', $this->integer());
        $this->addColumn('position', 'createdby', $this->integer());
        $this->addColumn('photo', 'createdby', $this->integer());
        $this->addColumn('response', 'createdby', $this->integer());
        $this->addColumn('request', 'createdby', $this->integer());
        $this->addColumn('order', 'createdby', $this->integer());
        $this->addColumn('delivery', 'createdby', $this->integer());
        $this->addColumn('materials', 'createdby', $this->integer());
        $this->addColumn('delivery_place', 'createdby', $this->integer());
        $this->addColumn('departure_place', 'createdby', $this->integer());
        $this->addColumn('project', 'createdby', $this->integer());
        $this->addColumn('job_stages', 'createdby', $this->integer());
        $this->addColumn('project_documents', 'createdby', $this->integer());

        // insert data in to the profile table
        $this->update('profile', ['createdby' => 1], ['id' => 1]);
        $this->update('profile', ['createdby' => 2], ['id' => 2]);
        $this->update('profile', ['createdby' => 3], ['id' => 3]);
        $this->update('profile', ['createdby' => 4], ['id' => 4]);
        $this->update('profile', ['createdby' => 5], ['id' => 5]);
        $this->update('profile', 'createdby', $this->integer()->notNull()]);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop author columns
        $this->dropColumn('profile', 'createdby');
        $this->dropColumn('contractor', 'createdby');
        $this->dropColumn('portfolio', 'createdby');
        $this->dropColumn('position', 'createdby');
        $this->dropColumn('photo', 'createdby');
        $this->dropColumn('response', 'createdby');
        $this->dropColumn('request', 'createdby');
        $this->dropColumn('order', 'createdby');
        $this->dropColumn('delivery', 'createdby');
        $this->dropColumn('materials', 'createdby');
        $this->dropColumn('delivery_place', 'createdby');
        $this->dropColumn('departure_place', 'createdby');
        $this->dropColumn('project', 'createdby');
        $this->dropColumn('job_stages', 'createdby');
        $this->dropColumn('project_documents', 'createdby');
    }
}
