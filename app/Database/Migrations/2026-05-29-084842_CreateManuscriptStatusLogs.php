<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscriptStatusLogs extends Migration
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
             * Previous Status
             */
            'old_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            /**
             * New Status
             */
            'new_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            /**
             * Status Change Remarks
             */
            'remarks' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            /**
             * Profile who changed status
             */
            'changed_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            /**
             * System Generated?
             */
            'is_system_generated' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
            'old_status'
        );

        $this->forge->addKey(
            'new_status'
        );

        $this->forge->addKey(
            'changed_by'
        );

        $this->forge->addKey(
            'created_at'
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
            'changed_by',
            'profiles',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->createTable(
            'manuscript_status_logs',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscript_status_logs',
            true
        );
    }
}