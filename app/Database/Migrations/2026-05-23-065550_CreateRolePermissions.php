<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolePermissions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'role_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'permission_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'created_by' => [
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
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addKey('role_id');
        $this->forge->addKey('permission_id');
        $this->forge->addKey('created_by');

        // Prevent duplicate role-permission assignments
        $this->forge->addUniqueKey([
            'role_id',
            'permission_id',
        ]);

        $this->forge->addForeignKey(
            'role_id',
            'roles',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'permission_id',
            'permissions',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('role_permissions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('role_permissions', true);
    }
}