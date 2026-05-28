<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEditorProfiles extends Migration
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

            'profile_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            'editor_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'designation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'organization_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'qualification' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'specialization' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'research_interests' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            'experience_years' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
            ],

            'bio' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],

            'profile_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'profile_slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'orcid_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'google_scholar_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'scopus_author_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'researchgate_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'linkedin_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'personal_website_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            'is_featured' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
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

        $this->forge->addKey(
            'id',
            true
        );

        $this->forge->addUniqueKey(
            'uuid'
        );

        $this->forge->addUniqueKey(
            'profile_slug'
        );

        $this->forge->addKey(
            'profile_id'
        );

        $this->forge->addKey(
            'editor_type'
        );

        $this->forge->addKey(
            'status'
        );

        $this->forge->addKey(
            'is_featured'
        );

        $this->forge->addKey(
            'sort_order'
        );

        $this->forge->addKey(
            'created_at'
        );

        /**
         * Foreign Keys
         */
        $this->forge->addForeignKey(
            'profile_id',
            'profiles',
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
            'editor_profiles',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'editor_profiles',
            true
        );
    }
}