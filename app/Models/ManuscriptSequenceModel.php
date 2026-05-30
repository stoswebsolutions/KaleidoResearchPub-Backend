<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptSequenceModel extends Model
{
    protected $table = 'manuscript_sequences';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'journal_id',

        'year',

        'last_number',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = '';

    protected $updatedField = 'updated_at';

    /**
     * Get Next Sequence Number.
     */
    public function getNextNumber(
        int $journalId,
        int $year
    ): int {

        $record = $this
            ->where(
                'journal_id',
                $journalId
            )
            ->where(
                'year',
                $year
            )
            ->first();

        if (! $record) {

            $this->insert([
                'journal_id'  => $journalId,
                'year'        => $year,
                'last_number' => 1,
            ]);

            return 1;
        }

        $nextNumber =
            ((int) $record['last_number']) + 1;

        $this->update(
            $record['id'],
            [
                'last_number' => $nextNumber,
            ]
        );

        return $nextNumber;
    }
}