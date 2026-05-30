<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscriptPublications extends Migration
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
             * Publication Details
             */
            'page_start' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],

            'page_end' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],

            'volume_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'issue_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'published_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            /**
             * monthly
             * quarterly
             * half_yearly
             * yearly
             */
            'frequency' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'published_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            /**
             * DOI Information
             */
            'doi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'article_url' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],

            /**
             * Publication Files
             */
            'published_pdf' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            /**
             * Published Status
             */
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'published',
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
         * One publication record per manuscript.
         */
        $this->forge->addUniqueKey(
            'manuscript_id'
        );

        /**
         * DOI should be unique.
         */
        $this->forge->addUniqueKey(
            'doi'
        );

        /**
         * Indexes
         */
        $this->forge->addKey(
            'volume_number'
        );

        $this->forge->addKey(
            'issue_number'
        );

        $this->forge->addKey(
            'frequency'
        );

        $this->forge->addKey(
            'published_date'
        );

        $this->forge->addKey(
            'status'
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

        $this->forge->createTable(
            'manuscript_publications',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscript_publications',
            true
        );
    }
}