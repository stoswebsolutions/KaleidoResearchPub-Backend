<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscriptTrackingOtps extends Migration
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
             * Corresponding author email.
             */
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            /**
             * OTP Code.
             */
            'otp' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],

            /**
             * OTP Expiry.
             */
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            /**
             * OTP Verification Time.
             */
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            /**
             * Track attempts.
             */
            'attempt_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            /**
             * Prevent OTP reuse.
             */
            'is_used' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            /**
             * IP tracking.
             */
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            /**
             * Audit
             */
            'created_at' => [
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
         * Unique Key
         */
        $this->forge->addUniqueKey(
            'uuid'
        );

        /**
         * Indexes
         */
        $this->forge->addKey(
            'manuscript_id'
        );

        $this->forge->addKey(
            'email'
        );

        $this->forge->addKey(
            'otp'
        );

        $this->forge->addKey(
            'expires_at'
        );

        $this->forge->addKey(
            'verified_at'
        );

        $this->forge->addKey(
            'is_used'
        );

        $this->forge->addKey(
            'created_at'
        );

        /**
         * Foreign Key
         */
        $this->forge->addForeignKey(
            'manuscript_id',
            'manuscripts',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable(
            'manuscript_tracking_otps',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscript_tracking_otps',
            true
        );
    }
}