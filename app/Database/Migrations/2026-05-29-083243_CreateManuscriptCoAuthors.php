<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscriptCoAuthors extends Migration
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

            'author_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'designation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'university_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
         * Indexes
         */
        $this->forge->addKey(
            'manuscript_id'
        );

        $this->forge->addKey(
            'email'
        );

        $this->forge->addKey(
            'sort_order'
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
            'manuscript_co_authors',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscript_co_authors',
            true
        );
    }
}