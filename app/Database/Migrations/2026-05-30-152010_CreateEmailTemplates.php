<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailTemplates extends Migration
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

            /**
             * Example:
             * manuscript-submitted
             * manuscript-accepted
             * otp-verification
             */
            'template_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'template_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'subject' => [
                'type' => 'TEXT',
            ],

            'content' => [
                'type' => 'LONGTEXT',
            ],

            /**
             * JSON Array
             *
             * Example:
             * ["author_name","manuscript_id"]
             */
            'variables' => [
                'type' => 'LONGTEXT',
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

        $this->forge->addUniqueKey(
            'template_key'
        );

        /**
         * Indexes
         */
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
            'email_templates',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'email_templates',
            true
        );
    }
}