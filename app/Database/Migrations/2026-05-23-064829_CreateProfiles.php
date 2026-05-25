<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProfiles extends Migration
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

            'role_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
            ],

            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'profile_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
            ],

            'email_verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'phone_verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'last_login_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'last_login_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],

            'failed_login_attempts' => [
                'type'    => 'INT',
                'default' => 0,
            ],

            'locked_until' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('phone');

        $this->forge->addKey('role_id');
        $this->forge->addKey('status');
        $this->forge->addKey('last_login_at');

        $this->forge->addKey('created_by');
        $this->forge->addKey('updated_by');
        $this->forge->addKey('deleted_by');

        $this->forge->addForeignKey(
            'role_id',
            'roles',
            'id',
            'CASCADE',
            'RESTRICT'
        );

        $this->forge->createTable('profiles', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('profiles', true);
    }
}