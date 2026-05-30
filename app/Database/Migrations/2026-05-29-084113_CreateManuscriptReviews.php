<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscriptReviews extends Migration
{
    public function up(): void
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'uuid' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],

            'manuscript_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            /**
             * Reviewer (Editor)
             */
            'editor_profile_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            /**
             * accepted
             * rejected
             * minor_revision
             * major_revision
             */
            'review_recommendation' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            /**
             * Review comments.
             */
            'comments' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            /**
             * Optional review report.
             */
            'review_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            /**
             * Editor review submitted date.
             */
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        /**
         * Primary Key
         */
        $this->forge->addKey(
            'id',
            true
        );

        /**
         * Unique Keys
         */
        $this->forge->addUniqueKey(
            'uuid'
        );

        /**
         * One editor review per manuscript.
         */
        $this->forge->addUniqueKey([
            'manuscript_id',
            'editor_profile_id',
        ]);

        /**
         * Indexes
         */
        $this->forge->addKey(
            'manuscript_id'
        );

        $this->forge->addKey(
            'editor_profile_id'
        );

        $this->forge->addKey(
            'review_recommendation'
        );

        $this->forge->addKey(
            'reviewed_at'
        );

        /**
         * Foreign Keys
         */
        $this->forge->addForeignKey(
            'manuscript_id',
            'manuscripts',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'editor_profile_id',
            'editor_profiles',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable(
            'manuscript_reviews',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscript_reviews',
            true
        );
    }
}