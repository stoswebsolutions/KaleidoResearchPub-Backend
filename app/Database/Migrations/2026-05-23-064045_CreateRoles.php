<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoles extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'uuid' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],

            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
            ],

            'is_system' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
            ],

            'created_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'updated_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'deleted_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
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
        $this->forge->addUniqueKey('name');
        $this->forge->addUniqueKey('slug');

        $this->forge->addKey('status');

        $this->forge->addKey('created_by');
        $this->forge->addKey('updated_by');
        $this->forge->addKey('deleted_by');

        $this->forge->createTable('roles', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('roles', true);
    }
}