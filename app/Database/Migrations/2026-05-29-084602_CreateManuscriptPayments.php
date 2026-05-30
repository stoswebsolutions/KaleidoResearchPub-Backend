<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscriptPayments extends Migration
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

            'manuscript_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            /**
             * Payment Information
             */
            'payment_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],

            'payment_reference_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'payment_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            /**
             * Uploaded Documents
             */
            'payment_screenshot' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'author_signature' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'author_id_proof' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            /**
             * Verification
             */
            'payment_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],

            'verification_remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'verified_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            /**
             * Audit Fields
             */
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
         * One payment record per manuscript.
         */
        $this->forge->addUniqueKey(
            'manuscript_id'
        );

        /**
         * Indexes
         */
        $this->forge->addKey(
            'payment_status'
        );

        $this->forge->addKey(
            'payment_reference_no'
        );

        $this->forge->addKey(
            'payment_date'
        );

        $this->forge->addKey(
            'verified_by'
        );

        $this->forge->addKey(
            'verified_at'
        );

        /**
         * Foreign Keys
         */
        $this->forge->addForeignKey(
            'manuscript_id',
            'manuscripts',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'verified_by',
            'profiles',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->createTable(
            'manuscript_payments',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscript_payments',
            true
        );
    }
}