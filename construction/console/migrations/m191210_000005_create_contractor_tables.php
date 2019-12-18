<?php

use yii\db\Migration;

/**
 * Class m191210_000005_create_contractor_tables
 */
class m191210_000005_create_contractor_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create contractor table
        $this->createTable('{{%contractor}}', [
            'id' => $this->primaryKey(),
            'profile_id' => $this->integer()->notNull(),
            'experience' => $this->string()->notNull(),
            'cost' => $this->string()->notNull()
        ]);

        // creates index for column profile_id
        $this->createIndex(
            'idx-contractor-profile-id',
            '{{%contractor}}',
            'profile_id'
        );

        // create attestation table
        $this->createTable('{{%attestation}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create contractor_attestation table
        $this->createTable('{{%contractor_attestation}}', [
            'contractor_id' => $this->integer()->notNull(),
            'attestation_id' => $this->integer()->notNull()
        ]);

        // creates index for column contractor_id
        $this->createIndex(
            'idx-contractor-attestation-contractor-id',
            '{{%contractor_attestation}}',
            'contractor_id'
        );

        // creates index for column attestation_id
        $this->createIndex(
            'idx-contractor-attestation-attestation-id',
            '{{%contractor_attestation}}',
            'attestation_id'
        );

        // create kind_job table
        $this->createTable('{{%kind_job}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create contractor_kind_job table
        $this->createTable('{{%contractor_kind_job}}', [
            'contractor_id' => $this->integer()->notNull(),
            'kind_job_id' => $this->integer()->notNull()
        ]);

        // creates index for column contractor_id
        $this->createIndex(
            'idx-contractor-kind-job-contractor-id',
            '{{%contractor_kind_job}}',
            'contractor_id'
        );

        // creates index for column kind_job_id
        $this->createIndex(
            'idx-contractor-kind-job-kind-job-id',
            '{{%contractor_kind_job}}',
            'kind_job_id'
        );

        // create portfolio table
        $this->createTable('{{%portfolio}}', [
            'id' => $this->primaryKey(),
            'contractor_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        // creates index for column contractor_id
        $this->createIndex(
            'idx-portfolio-contractor-id',
            '{{%portfolio}}',
            'contractor_id'
        );

        // create position table
        $this->createTable('{{%position}}', [
            'id' => $this->primaryKey(),
            'portfolio_id' => $this->integer()->notNull(),
            'description' => $this->string()->notNull()
        ]);

        // creates index for column portfolio_id
        $this->createIndex(
            'idx-position-portfolio-id',
            '{{%position}}',
            'portfolio_id'
        );

        // add foreign key for table contractor
        $this->addForeignKey(
            'fk-contractor_profile-id',
            '{{%contractor}}',
            'profile_id',
            '{{%profile}}',
            'id'
        );

        // add foreign keys for table contractor_attestation
        $this->addForeignKey(
            'fk-contractor_attestation-contractor-id',
            '{{%contractor_attestation}}',
            'contractor_id',
            '{{%contractor}}',
            'id'
        );
        $this->addForeignKey(
            'fk-contractor_attestation-attestation-id',
            '{{%contractor_attestation}}',
            'attestation_id',
            '{{%attestation}}',
            'id'
        );

        // add foreign keys for table contractor_kind_job
        $this->addForeignKey(
            'fk-contractor-kind-job-contractor-id',
            '{{%contractor_kind_job}}',
            'contractor_id',
            '{{%contractor}}',
            'id'
        );
        $this->addForeignKey(
            'fk-contractor-kind-job-kind-job-id',
            '{{%contractor_kind_job}}',
            'kind_job_id',
            '{{%kind_job}}',
            'id'
        );

        // add foreign key for table portfolio
        $this->addForeignKey(
            'fk-portfolio-contractor-id',
            '{{%portfolio}}',
            'contractor_id',
            '{{%contractor}}',
            'id'
        );

        // add foreign key for table position
        $this->addForeignKey(
            'fk-position-portfolio-id',
            '{{%position}}',
            'portfolio_id',
            '{{%portfolio}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table position
        $this->dropForeignKey(
            'fk-position-portfolio-id',
            '{{%position}}'
        );

        // drops foreign key for table portfolio
        $this->dropForeignKey(
            'fk-portfolio-contractor-id',
            '{{%portfolio}}'
        );

        // drops foreign keys for table contractor-kind-job
        $this->dropForeignKey(
            'fk-contractor-kind-job-contractor-id',
            '{{%contractor_kind_job}}'
        );
        $this->dropForeignKey(
            'fk-contractor-kind-job-kind-job-id',
            '{{%contractor_kind_job}}'
        );

        // drops foreign keys for table contractor_attestation
        $this->dropForeignKey(
            'fk-contractor_attestation-contractor-id',
            '{{%contractor_attestation}}'
        );
        $this->dropForeignKey(
            'fk-contractor_attestation-attestation-id',
            '{{%contractor_attestation}}'
        );

        // drops foreign key for table contractor
        $this->dropForeignKey(
            'fk-contractor_profile-id',
            '{{%contractor}}'
        );

        // drop index for column position
        $this->dropIndex(
            'idx-position-portfolio-id',
            '{{%position}}'
        );

        // drop index for column portfolio
        $this->dropIndex(
            'idx-portfolio-contractor-id',
            '{{%portfolio}}'
        );

        // drop index for column kind_job_id
        $this->dropIndex(
            'idx-contractor-kind-job-kind-job-id',
            '{{%contractor_kind_job}}'
        );

        // drop index for column contractor_id
        $this->dropIndex(
            'idx-contractor-kind-job-contractor-id',
            '{{%contractor_kind_job}}'
        );

        // drop index for column attestation-id
        $this->dropIndex(
            'idx-contractor-attestation-attestation-id',
            '{{%contractor_attestation}}'
        );

        // drop index for column contractor-id
        $this->dropIndex(
            'idx-contractor-attestation-contractor-id',
            '{{%contractor_attestation}}'
        );

        // drop index for column profile-id
        $this->dropIndex(
            'idx-contractor-profile-id',
            '{{%contractor}}'
        );

        // drop position table
        $this->dropTable('{{%position}}');

        // drop portfolio table
        $this->dropTable('{{%portfolio}}');

        // drop contractor_kind_job table
        $this->dropTable('{{%contractor_kind_job}}');

        // drop kind_job table
        $this->dropTable('{{%kind_job}}');

        // drop contractor_attestation table
        $this->dropTable('{{%contractor_attestation}}');

        // drop attestation table
        $this->dropTable('{{%attestation}}');

        // drop contractor table
        $this->dropTable('{{%contractor}}');
    }
}
