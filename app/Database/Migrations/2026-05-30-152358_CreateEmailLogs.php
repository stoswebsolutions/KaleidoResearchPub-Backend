<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailLogs extends Migration
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
             * Related Template
             */
            'email_template_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            /**
             * Recipient
             */
            'recipient_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'recipient_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            /**
             * Email Content
             */
            'subject' => [
                'type' => 'TEXT',
            ],

            'message' => [
                'type' => 'LONGTEXT',
            ],

            /**
             * Delivery Status
             */
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],

            /**
             * SMTP Error
             */
            'error_message' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            /**
             * Mail Sent Date
             */
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            /**
             * Audit Fields
             */
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
            'email_template_id'
        );

        $this->forge->addKey(
            'recipient_email'
        );

        $this->forge->addKey(
            'status'
        );

        $this->forge->addKey(
            'sent_at'
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
            'email_template_id',
            'email_templates',
            'id',
            'SET NULL',
            'CASCADE'
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
            'email_logs',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'email_logs',
            true
        );
    }
}