<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJournalEditors extends Migration
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
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => false,
            ],

            'journal_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],

            'editor_profile_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],

            'editor_role' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'editor_in_chief',
                    'editor',
                    'managing_editor',
                    'associate_editor',
                    'editorial_board_member',
                    'review_editor',
                    'guest_editor',
                ],
                'default' => 'editorial_board_member',
            ],

            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'active',
                    'inactive',
                ],
                'default' => 'active',
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
            'uuid',
            'journal_editors_uuid_unique'
        );

        /**
         * Prevent duplicate editor assignment
         * to same journal.
         */
        $this->forge->addUniqueKey(
            [
                'journal_id',
                'editor_profile_id',
            ],
            'journal_editor_unique'
        );

        $this->forge->addKey(
            'journal_id',
            false,
            false,
            'journal_editors_journal_id_index'
        );

        $this->forge->addKey(
            'editor_profile_id',
            false,
            false,
            'journal_editors_editor_profile_id_index'
        );

        $this->forge->addKey(
            'editor_role',
            false,
            false,
            'journal_editors_editor_role_index'
        );

        $this->forge->addKey(
            'status',
            false,
            false,
            'journal_editors_status_index'
        );

        $this->forge->createTable(
            'journal_editors',
            true
        );

        /**
         * Foreign Keys
         */
        $this->db->query(
            'ALTER TABLE journal_editors
            ADD CONSTRAINT fk_journal_editors_journal
            FOREIGN KEY (journal_id)
            REFERENCES journals(id)
            ON UPDATE CASCADE
            ON DELETE CASCADE'
        );

        $this->db->query(
            'ALTER TABLE journal_editors
            ADD CONSTRAINT fk_journal_editors_editor
            FOREIGN KEY (editor_profile_id)
            REFERENCES editor_profiles(id)
            ON UPDATE CASCADE
            ON DELETE CASCADE'
        );

        $this->db->query(
            'ALTER TABLE journal_editors
            ADD CONSTRAINT fk_journal_editors_created_by
            FOREIGN KEY (created_by)
            REFERENCES profiles(id)
            ON UPDATE CASCADE
            ON DELETE SET NULL'
        );

        $this->db->query(
            'ALTER TABLE journal_editors
            ADD CONSTRAINT fk_journal_editors_updated_by
            FOREIGN KEY (updated_by)
            REFERENCES profiles(id)
            ON UPDATE CASCADE
            ON DELETE SET NULL'
        );

        $this->db->query(
            'ALTER TABLE journal_editors
            ADD CONSTRAINT fk_journal_editors_deleted_by
            FOREIGN KEY (deleted_by)
            REFERENCES profiles(id)
            ON UPDATE CASCADE
            ON DELETE SET NULL'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'journal_editors',
            true
        );
    }
}