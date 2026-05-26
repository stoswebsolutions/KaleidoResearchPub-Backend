<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJournals extends Migration
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

            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'short_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'thumbnail' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'description' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            'aims_scope' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            'issn_print' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            'issn_online' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            'doi_prefix' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'impact_factor' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,3',
                'null'       => true,
            ],

            'frequency' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'publication_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'subject_area' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'peer_review_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'is_indexed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            'year_started' => [
                'type' => 'YEAR',
                'null' => true,
            ],

            'website_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'contact_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
                'null'       => true,
            ],

            'contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],

            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'draft',
            ],

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

        $this->forge->addKey('id', true);

        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey('slug');

        $this->forge->addKey('title');
        $this->forge->addKey('short_title');

        $this->forge->addKey('issn_print');
        $this->forge->addKey('issn_online');

        $this->forge->addKey('status');
        $this->forge->addKey('year_started');

        $this->forge->addKey('created_by');
        $this->forge->addKey('updated_by');
        $this->forge->addKey('deleted_by');

        $this->forge->addForeignKey(
            'created_by',
            'profiles',
            'id',
            'CASCADE',
            'SET NULL'
        );

        $this->forge->addForeignKey(
            'updated_by',
            'profiles',
            'id',
            'CASCADE',
            'SET NULL'
        );

        $this->forge->addForeignKey(
            'deleted_by',
            'profiles',
            'id',
            'CASCADE',
            'SET NULL'
        );

        $this->forge->createTable('journals', true);
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'journals',
            true
        );
    }
}