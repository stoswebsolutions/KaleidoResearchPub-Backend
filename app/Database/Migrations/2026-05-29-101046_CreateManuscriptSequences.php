<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManuscriptSequences extends Migration
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

            'journal_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            'year' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
            ],

            'last_number' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey(
            'id',
            true
        );

        /**
         * One sequence per journal/year.
         */
        $this->forge->addUniqueKey([
            'journal_id',
            'year',
        ]);

        $this->forge->addKey(
            'journal_id'
        );

        $this->forge->addKey(
            'year'
        );

        $this->forge->addForeignKey(
            'journal_id',
            'journals',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable(
            'manuscript_sequences',
            true
        );
    }

    public function down(): void
    {
        $this->forge->dropTable(
            'manuscript_sequences',
            true
        );
    }
}