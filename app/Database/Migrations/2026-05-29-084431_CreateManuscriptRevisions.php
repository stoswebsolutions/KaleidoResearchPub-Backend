<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscriptRevisions extends Migration
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
             * Revision Number
             * Example:
             * 1, 2, 3...
             */
            'revision_no' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],

            /**
             * Author response.
             */
            'revision_notes' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            /**
             * Updated manuscript file.
             */
            'paper_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            /**
             * Submitted by author.
             */
            'submitted_at' => [
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
         * Unique Key
         */
        $this->forge->addUniqueKey(
            'uuid'
        );

        /**
         * Prevent duplicate revision numbers
         * for same manuscript.
         */
        $this->forge->addUniqueKey([
            'manuscript_id',
            'revision_no',
        ]);

        /**
         * Indexes
         */
        $this->forge->addKey(
            'manuscript_id'
        );

        $this->forge->addKey(
            'revision_no'
        );

        $this->forge->addKey(
            'submitted_at'
        );

        /**
         * Foreign Key
         */
        $this->forge->addForeignKey(
            'manuscript_id',
            'manuscripts',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable(
            'manuscript_revisions',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscript_revisions',
            true
        );
    }
}