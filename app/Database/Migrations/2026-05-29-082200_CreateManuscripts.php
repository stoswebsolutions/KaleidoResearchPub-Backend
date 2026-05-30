<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscripts extends Migration
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

            /**
             * Example:
             * IJCSR-2026-000001
             */
            'manuscript_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            /**
             * NULL for guest submission.
             */
            'profile_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            'journal_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            'article_type_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            'disciplinary_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            /**
             * Corresponding Author
             */
            'corresponding_author_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'corresponding_author_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'corresponding_author_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            /**
             * Paper Details
             */
            'title' => [
                'type' => 'TEXT',
            ],

            'abstract' => [
                'type' => 'LONGTEXT',
            ],

            /**
             * Address Details
             */
            'university_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'pincode' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            'landmark' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            /**
             * Files
             */
            'paper_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'abstract_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            /**
             * Submission Source
             */
            'submission_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'guest',
            ],

            /**
             * Workflow Status
             */
            'current_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'submitted',
            ],

            'final_decision' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            
            'decision_remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'decision_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            'decision_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'revision_round' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'doi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            /**
             * Audit Fields
             */
            'created_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            'updated_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            'deleted_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
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

        $this->forge->addUniqueKey(
            'manuscript_id'
        );

        /**
         * Indexes
         */
        $this->forge->addKey(
            'profile_id'
        );

        $this->forge->addKey(
            'journal_id'
        );

        $this->forge->addKey(
            'article_type_id'
        );

        $this->forge->addKey(
            'disciplinary_id'
        );

        $this->forge->addKey(
            'corresponding_author_email'
        );

        $this->forge->addKey(
            'submission_source'
        );

        $this->forge->addKey(
            'current_status'
        );

        $this->forge->addKey(
            'final_decision'
        );

        $this->forge->addKey(
            'decision_by'
        );

        $this->forge->addKey(
            'doi'
        );

        $this->forge->addKey(
            'revision_round'
        );

        $this->forge->addKey(
            'submitted_at'
        );

        /**
         * Foreign Keys
         */
        $this->forge->addForeignKey(
            'profile_id',
            'profiles',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'journal_id',
            'journals',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'article_type_id',
            'article_types',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'disciplinary_id',
            'disciplines',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'decision_by',
            'profiles',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'created_by',
            'profiles',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'updated_by',
            'profiles',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'deleted_by',
            'profiles',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->createTable(
            'manuscripts',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscripts',
            true
        );
    }
}