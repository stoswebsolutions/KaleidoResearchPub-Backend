<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProfileSessions extends Migration
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

            'refresh_token_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'device_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'device_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],

            'browser' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],

            'login_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'password',
            ],

            'last_activity_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],

            'login_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],

            'logout_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'revoked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'is_active' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
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

        $this->forge->addUniqueKey('uuid');

        $this->forge->addKey('profile_id');
        $this->forge->addKey('refresh_token_hash');
        $this->forge->addKey('is_active');
        $this->forge->addKey('expires_at');

        $this->forge->addForeignKey(
            'profile_id',
            'profiles',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('profile_sessions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('profile_sessions', true);
    }
}