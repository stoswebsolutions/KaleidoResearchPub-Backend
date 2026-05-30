<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscriptEditorAssignments extends Migration
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

            'editor_profile_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            /**
             * chief_editor
             * editor
             */
            'editor_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],

            /**
             * When system assigned editor.
             */
            'assigned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            /**
             * assigned
             * accepted
             * declined
             * completed
             */
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'assigned',
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
         * Unique Key
         */
        $this->forge->addUniqueKey(
            'uuid'
        );

        /**
         * Prevent duplicate assignment
         */
        $this->forge->addUniqueKey([
            'manuscript_id',
            'editor_profile_id',
        ]);

        /**
         * Indexes
         */
        $this->forge->addKey(
            'manuscript_id'
        );

        $this->forge->addKey(
            'editor_profile_id'
        );

        $this->forge->addKey(
            'editor_role'
        );

        $this->forge->addKey(
            'status'
        );

        $this->forge->addKey(
            'assigned_at'
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
            'editor_profile_id',
            'editor_profiles',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable(
            'manuscript_editor_assignments',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscript_editor_assignments',
            true
        );
    }
}