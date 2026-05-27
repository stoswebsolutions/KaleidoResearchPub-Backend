<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContactSettings extends Migration
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

            'organization_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'address' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'alternate_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'alternate_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'whatsapp' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'google_map_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],

            'facebook_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'twitter_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'linkedin_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'instagram_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'youtube_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'working_hours' => [
                'type' => 'TEXT',
                'null' => true,
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
            'created_by'
        );

        $this->forge->addKey(
            'updated_by'
        );

        $this->forge->addKey(
            'deleted_by'
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
            'contact_settings',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'contact_settings',
            true
        );
    }
}