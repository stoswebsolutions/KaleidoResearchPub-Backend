<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProfilePermissions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'profile_id' => [
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

        $this->forge->addKey('profile_id');
        $this->forge->addKey('permission_id');
        $this->forge->addKey('created_by');

        // Prevent duplicate assignments
        $this->forge->addUniqueKey([
            'profile_id',
            'permission_id',
        ]);

        $this->forge->addForeignKey(
            'profile_id',
            'profiles',
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

        $this->forge->createTable('profile_permissions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('profile_permissions', true);
    }
}