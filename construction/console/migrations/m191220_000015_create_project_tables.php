<?php

use yii\db\Migration;

/**
 * Class m191220_000015_create_project_tables
 */
class m191220_000015_create_project_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create project_documents table
        $this->createTable('{{%project_documents}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string()->notNull(),
            'path' => $this->string()->notNull()
        ]);

        // creates index for column project_id
        $this->createIndex(
            'idx-project-documents-project-id',
            '{{%project_documents}}',
            'project_id'
        );

        // create job_stages table
        $this->createTable('{{%job_stages}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'period' => $this->integer()->notNull(),
            'cost' => $this->float()->notNull()
        ]);

        // create project table
        $this->createTable('{{%project}}', [
            'id' => $this->primaryKey(),
            'job_stages_id' => $this->integer()->notNull(),
            'period' => $this->integer()->notNull(),
            'cost' => $this->float()->notNull()
        ]);

        // creates index for column job_stages_id
        $this->createIndex(
            'idx-project-job-stages-id',
            '{{%project}}',
            'job_stages_id'
        );

        // add foreign keys for table project
        $this->addForeignKey(
            'fk-project-job-stages-id',
            '{{%project}}',
            'job_stages_id',
            '{{%job_stages}}',
            'id'
        );

        // add foreign keys for table project_documents
        $this->addForeignKey(
            'fk-project-documents-project-id',
            '{{%project_documents}}',
            'project_id',
            '{{%project}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign keys for table project_documents
        $this->dropForeignKey(
            'fk-project-documents-project-id',
            '{{%project_documents}}'
        );

        // drops foreign keys for table project
        $this->dropForeignKey(
            'fk-project-job-stages-id',
            '{{%project}}'
        );

        // drop index for column job_stages_id
        $this->dropIndex(
            'idx-project-job-stages-id',
            '{{%project}}'
        );

        // drop index for column project_id
        $this->dropIndex(
            'idx-project-documents-project-id',
            '{{%project_documents}}'
        );

        // drop project_documents table
        $this->dropTable('{{%project_documents}}');

        // drop job_stages table
        $this->dropTable('{{%job_stages}}');

        // drop project table
        $this->dropTable('{{%project}}');
    }
}
