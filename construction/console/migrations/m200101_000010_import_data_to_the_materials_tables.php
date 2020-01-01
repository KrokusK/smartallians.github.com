<?php

use yii\db\Migration;
//use yii\db\Command;

/**
 * Class m200101_000010_import_data_to_the_materials_tables
 */
class m200101_000010_import_data_to_the_materials_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // erase table records and sequences before to insert
        //$this->db->createCommand()->delete($table->user)->execute();
        //if ($table->sequenceName !== null) {
        //    $this->db->createCommand()->resetSequence($table->user, 1)->execute();
        //}
        $this->delete('{{%material_type}}');
        $this->delete('{{%status_material}}');
        $this->delete('{{%materials}}');

        $this->db->createCommand()->resetSequence('{{%material_type}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%status_material}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%materials}}', 1)->execute();


        // import to the status_material table
        $this->insert('{{%status_material}}', [
            'name' => 'Заказан',
        ]);
        $this->insert('{{%status_material}}', [
            'name' => 'Отправлен',
        ]);
        $this->insert('{{%status_material}}', [
            'name' => 'В наличие',
        ]);


        // import to the material_type table
        $this->insert('{{%material_type}}', [
            'name' => 'Кирпич',
        ]);
        $this->insert('{{%material_type}}', [
            'name' => 'Бревна',
        ]);
        $this->insert('{{%material_type}}', [
            'name' => 'Арматура',
        ]);

        // import to the materials table
        $this->insert('{{%materials}}', [
            'request_id' => 1,
            'delivery_id' => null,
            '%material_type_id' => 1,
            '%status_material_id' => 1,
            'name' => 'Кирпич для бани',
            'count' => 50,
            'cost' => 250,
            'created_by' => 1
        ]);
        $this->insert('{{%materials}}', [
            'request_id' => 2,
            'delivery_id' => null,
            '%material_type_id' => 2,
            '%status_material_id' => 1,
            'name' => 'Брено для пристройки',
            'count' => 30,
            'cost' => 1000,
            'created_by' => 2
        ]);
        $this->insert('{{%materials}}', [
            'request_id' => 2,
            'delivery_id' => null,
            '%material_type_id' => 3,
            '%status_material_id' => 1,
            'name' => 'Арматура для дома',
            'count' => 20,
            'cost' => 500,
            'created_by' => 2
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // erase table records and sequences
        $this->delete('{{%materials}}');
        $this->delete('{{%status_material}}');
        $this->delete('{{%material_type}}');

        $this->db->createCommand()->resetSequence('{{%materials}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%status_material}}', 1)->execute();
        $this->db->createCommand()->resetSequence('{{%material_type}}', 1)->execute();
    }
}
