<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailSettings extends Migration
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

            'mail_driver' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'smtp',
            ],

            'smtp_host' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'smtp_port' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true,
            ],

            'smtp_user' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'smtp_pass' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],

            'smtp_crypto' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            'from_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'from_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'reply_to_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'reply_to_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'is_default' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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

        $this->forge->addKey(
            'status'
        );

        $this->forge->addKey(
            'is_default'
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

        $this->forge->addForeignKey(
            'deleted_by',
            'profiles',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->createTable(
            'email_settings',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'email_settings',
            true
        );
    }
}