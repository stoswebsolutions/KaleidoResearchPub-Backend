<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePasswordResets extends Migration
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

            'token_hash' => [
                'type'       => 'CHAR',
                'constraint' => 64,
            ],

            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],

            'used_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('token_hash');
        $this->forge->addKey('expires_at');

        $this->forge->addForeignKey(
            'profile_id',
            'profiles',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('password_resets', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('password_resets', true);
    }
}