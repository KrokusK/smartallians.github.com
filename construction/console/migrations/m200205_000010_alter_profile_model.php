<?php

use yii\db\Migration;

/**
 * Class m200205_000010_alter_profile_model
 */
class m200205_000010_alter_profile_model extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create author columns
        $this->addColumn('profile', 'last_name', $this->string());
        $this->addColumn('profile', 'first_name', $this->string());
        $this->addColumn('profile', 'middle_name', $this->string());
        
        // insert data in to the profile table
        $this->update('profile', ['last_name' => 'Иванов'], ['id' => 1]);
        $this->update('profile', ['first_name' => 'Иван'], ['id' => 1]);
        $this->update('profile', ['middle_name' => 'Иванович'], ['id' => 1]);
        $this->update('profile', ['last_name' => 'Петров'], ['id' => 2]);
        $this->update('profile', ['first_name' => 'Петр'], ['id' => 2]);
        $this->update('profile', ['middle_name' => 'Петрович'], ['id' => 2]);
        $this->update('profile', ['last_name' => 'Сидоров'], ['id' => 3]);
        $this->update('profile', ['first_name' => 'Сидр'], ['id' => 3]);
        $this->update('profile', ['middle_name' => 'Сидорович'], ['id' => 3]);
        $this->update('profile', ['last_name' => 'Путин'], ['id' => 4]);
        $this->update('profile', ['first_name' => 'Владимир'], ['id' => 4]);
        $this->update('profile', ['middle_name' => 'Владимирович'], ['id' => 4]);
        $this->update('profile', ['last_name' => 'Медведев'], ['id' => 5]);
        $this->update('profile', ['first_name' => 'Дмитрий'], ['id' => 5]);
        $this->update('profile', ['middle_name' => 'Анатольевич'], ['id' => 5]);
        $this->update('profile', ['last_name' => 'Иванов'], ['id' => 6]);
        $this->update('profile', ['first_name' => 'Иван'], ['id' => 6]);
        $this->update('profile', ['middle_name' => 'Иванович'], ['id' => 6]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop author columns
        $this->dropColumn('profile', 'last_name');
        $this->dropColumn('profile', 'first_name');
        $this->dropColumn('profile', 'middle_name');
    }
}
