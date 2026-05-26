<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDisciplines extends Migration
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

            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'description' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            'parent_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
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

        $this->forge->addKey(
            'id',
            true
        );

        $this->forge->addUniqueKey(
            'uuid'
        );

        $this->forge->addUniqueKey(
            'slug'
        );

        $this->forge->addKey(
            'title'
        );

        $this->forge->addKey(
            'parent_id'
        );

        $this->forge->addKey(
            'status'
        );

        $this->forge->addKey(
            'sort_order'
        );

        $this->forge->addKey(
            'created_by'
        );

        $this->forge->addKey(
            'updated_by'
        );

        $this->forge->addKey(
            'deleted_by'
        );

        /**
         * Self Reference
         */
        $this->forge->addForeignKey(
            'parent_id',
            'disciplines',
            'id',
            'CASCADE',
            'SET NULL'
        );

        /**
         * Audit Fields
         */
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

        $this->forge->createTable(
            'disciplines',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'disciplines',
            true
        );
    }
}