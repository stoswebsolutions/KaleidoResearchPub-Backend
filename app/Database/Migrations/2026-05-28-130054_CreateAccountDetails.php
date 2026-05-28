<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccountDetails extends Migration
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

            'account_holder_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'account_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'bank_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'branch_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'branch_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'ifsc_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],

            'account_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'savings',
            ],

            'upi_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'qr_code_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'is_primary' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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

        /**
         * Primary Key
         */
        $this->forge->addKey(
            'id',
            true
        );

        /**
         * Unique Keys
         */
        $this->forge->addUniqueKey(
            'uuid'
        );

        /**
         * Indexes
         */
        $this->forge->addKey(
            'account_number'
        );

        $this->forge->addKey(
            'ifsc_code'
        );

        $this->forge->addKey(
            'account_type'
        );

        $this->forge->addKey(
            'is_primary'
        );

        $this->forge->addKey(
            'sort_order'
        );

        $this->forge->addKey(
            'status'
        );

        $this->forge->addKey(
            'created_at'
        );

        /**
         * Foreign Keys
         */
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
            'account_details',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'account_details',
            true
        );
    }
}