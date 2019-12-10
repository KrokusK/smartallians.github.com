<?php

use yii\db\Migration;

/**
 * Class m191210_000005_create_constractor_tables
 */
class m191210_000005_create_constractor_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // create constractor table
        $this->createTable('{{%constractor}}', [
            'id' => $this->primaryKey(),
            'profile_id' => $this->integer()->notNull(),
            'experience' => $this->string()->notNull(),
            'cost' => $this->string()->notNull()
        ]);

        // creates index for column profile_id
        $this->createIndex(
            'idx-constractor-profile-id',
            '{{%constractor}}',
            'profile_id'
        );

        // create attestation table
        $this->createTable('{{%attestation}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create constractor_attestation table
        $this->createTable('{{%constractor_attestation}}', [
            'constractor_id' => $this->integer()->notNull(),
            'attestation_id' => $this->integer()->notNull()
        ]);

        // creates index for column constractor_id
        $this->createIndex(
            'idx-constractor-attestation-constractor-id',
            '{{%constractor_attestation}}',
            'constractor_id'
        );

        // creates index for column attestation_id
        $this->createIndex(
            'idx-constractor-attestation-attestation-id',
            '{{%constractor_attestation}}',
            'attestation_id'
        );

        // create kind_job table
        $this->createTable('{{%kind_job}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        // create constractor_kind_job table
        $this->createTable('{{%constractor_kind_job}}', [
            'constractor_id' => $this->integer()->notNull(),
            'kind_job_id' => $this->integer()->notNull()
        ]);

        // creates index for column constractor_id
        $this->createIndex(
            'idx-constractor-kind-job-constractor-id',
            '{{%constractor_kind_job}}',
            'constractor_id'
        );

        // creates index for column kind_job_id
        $this->createIndex(
            'idx-constractor-kind-job-kind-job-id',
            '{{%constractor_kind_job}}',
            'kind_job_id'
        );

        // create portfolio table
        $this->createTable('{{%portfolio}}', [
            'id' => $this->primaryKey(),
            'constractor_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        // creates index for column constractor_id
        $this->createIndex(
            'idx-portfolio-constractor-id',
            '{{%portfolio}}',
            'constractor_id'
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

        // add foreign key for table constractor
        $this->addForeignKey(
            'fk-constractor_profile-id',
            '{{%constractor}}',
            'profile_id',
            '{{%profile}}',
            'id'
        );

        // add foreign keys for table constractor_attestation
        $this->addForeignKey(
            'fk-constractor_attestation-constractor-id',
            '{{%constractor_attestation}}',
            'constractor_id',
            '{{%constractor}}',
            'id'
        );
        $this->addForeignKey(
            'fk-constractor_attestation-attestation-id',
            '{{%constractor_attestation}}',
            'attestation_id',
            '{{%attestation}}',
            'id'
        );

        // add foreign keys for table constractor_kind_job
        $this->addForeignKey(
            'fk-constractor-kind-job-constractor-id',
            '{{%constractor_kind_job}}',
            'constractor_id',
            '{{%constractor}}',
            'id'
        );
        $this->addForeignKey(
            'fk-constractor-kind-job-kind-job-id',
            '{{%constractor_kind_job}}',
            'kind_job_id',
            '{{%kind_job}}',
            'id'
        );

        // add foreign key for table portfolio
        $this->addForeignKey(
            'fk-portfolio-constractor-id',
            '{{%portfolio}}',
            'constractor_id',
            '{{%constractor}}',
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
            'fk-portfolio-constractor-id',
            '{{%portfolio}}'
        );

        // drops foreign keys for table constractor-kind-job
        $this->dropForeignKey(
            'fk-constractor-kind-job-constractor-id',
            '{{%constractor_kind_job}}'
        );
        $this->dropForeignKey(
            'fk-constractor-kind-job-kind-job-id',
            '{{%constractor_kind_job}}'
        );

        // drops foreign keys for table constractor_attestation
        $this->dropForeignKey(
            'fk-constractor_attestation-constractor-id',
            '{{%constractor_attestation}}'
        );
        $this->dropForeignKey(
            'fk-constractor_attestation-attestation-id',
            '{{%constractor_attestation}}'
        );

        // drops foreign key for table constractor
        $this->dropForeignKey(
            'fk-constractor_profile-id',
            '{{%constractor}}'
        );

        // drop index for column position
        $this->dropIndex(
            'idx-position-portfolio-id',
            '{{%position}}'
        );

        // drop index for column portfolio
        $this->dropIndex(
            'idx-portfolio-constractor-id',
            '{{%portfolio}}'
        );

        // drop index for column kind_job_id
        $this->dropIndex(
            'idx-constractor-kind-job-kind-job-id',
            '{{%constractor_kind_job}}'
        );

        // drop index for column constractor_id
        $this->dropIndex(
            'idx-constractor-kind-job-constractor-id',
            '{{%constractor_kind_job}}'
        );

        // drop index for column attestation-id
        $this->dropIndex(
            'idx-constractor-attestation-attestation-id',
            '{{%constractor_attestation}}'
        );

        // drop index for column constractor-id
        $this->dropIndex(
            'idx-constractor-attestation-constractor-id',
            '{{%constractor_attestation}}'
        );

        // drop index for column profile-id
        $this->dropIndex(
            'idx-constractor-profile-id',
            '{{%constractor}}'
        );

        // drop position table
        $this->dropTable('{{%position}}');

        // drop portfolio table
        $this->dropTable('{{%portfolio}}');

        // drop constractor_kind_job table
        $this->dropTable('{{%constractor_kind_job}}');

        // drop kind_job table
        $this->dropTable('{{%kind_job}}');

        // drop constractor_attestation table
        $this->dropTable('{{%constractor_attestation}}');

        // drop attestation table
        $this->dropTable('{{%attestation}}');

        // drop constractor table
        $this->dropTable('{{%constractor}}');
    }
}
