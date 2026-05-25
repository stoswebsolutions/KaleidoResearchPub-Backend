<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogs extends Migration
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

            'profile_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'module' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'record_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'old_values' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            'new_values' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],

            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addUniqueKey('uuid');

        $this->forge->addKey('profile_id');
        $this->forge->addKey('module');
        $this->forge->addKey('action');
        $this->forge->addKey('record_id');
        $this->forge->addKey('created_at');

        $this->forge->addForeignKey(
            'profile_id',
            'profiles',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('activity_logs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('activity_logs', true);
    }
}