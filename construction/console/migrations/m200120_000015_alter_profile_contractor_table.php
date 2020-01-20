<?php

use yii\db\Migration;

/**
 * Class m200116_000015_alter_materials_table
 */
class m200120_000015_alter_profile_contractor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // alter profile table
        $this->addColumn('profile', 'about', $this->string(512));

        // alter contrator table
        $this->addColumn('contractor', 'passport', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // alter profile table
        $this->dropColumn('profile', 'about');

        // alter contrator table
        $this->dropColumn('contractor', 'passport');
    }
}
