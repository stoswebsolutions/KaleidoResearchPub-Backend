<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptRevisionModel extends Model
{
    protected $table = 'manuscript_revisions';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'revision_no',

        'revision_notes',

        'paper_file',

        'submitted_at',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'manuscript_id' => 'integer',

        'revision_no' => 'integer',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    protected $validationRules = [

        'manuscript_id' => [
            'label' => 'Manuscript',
            'rules' => 'required|integer',
        ],

        'revision_no' => [
            'label' => 'Revision Number',
            'rules' => 'required|integer|greater_than[0]',
        ],

        'paper_file' => [
            'label' => 'Paper File',
            'rules' => 'required|max_length[255]',
        ],

        'revision_notes' => [
            'label' => 'Revision Notes',
            'rules' => 'permit_empty',
        ],
    ];

    protected $validationMessages = [

        'manuscript_id' => [
            'required' =>
                'Manuscript is required.',
        ],

        'revision_no' => [
            'required' =>
                'Revision number is required.',

            'integer' =>
                'Revision number must be numeric.',

            'greater_than' =>
                'Revision number must be greater than zero.',
        ],

        'paper_file' => [
            'required' =>
                'Revised manuscript file is required.',
        ],
    ];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    protected $beforeInsert = [
        'generateUuid',
    ];

    /**
     * Generate UUID.
     */
    protected function generateUuid(
        array $data
    ): array {

        if (
            empty(
                $data['data']['uuid']
            )
        ) {

            $data['data']['uuid'] =
                generate_uuid();
        }

        return $data;
    }

    /**
     * Find By UUID.
     */
    public function findByUuid(
        string $uuid
    ): ?array {

        return $this->where(
            'uuid',
            $uuid
        )->first();
    }

    /**
     * Get Revisions By Manuscript.
     */
    public function getByManuscript(
        int $manuscriptId
    ): array {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )
        ->orderBy(
            'revision_no',
            'DESC'
        )
        ->findAll();
    }

    /**
     * Get Latest Revision.
     */
    public function getLatestRevision(
        int $manuscriptId
    ): ?array {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->orderBy(
                'revision_no',
                'DESC'
            )
            ->first();
    }

    /**
     * Get Next Revision Number.
     */
    public function getNextRevisionNumber(
        int $manuscriptId
    ): int {

        $latestRevision = $this
            ->where(
                'manuscript_id',
                $manuscriptId
            )
            ->selectMax(
                'revision_no'
            )
            ->first();

        return (
            (int) (
                $latestRevision['revision_no']
                ?? 0
            )
        ) + 1;
    }

    /**
     * Check Revision Exists.
     */
    public function revisionExists(
        int $manuscriptId,
        int $revisionNo
    ): bool {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'revision_no',
                $revisionNo
            )
            ->countAllResults() > 0;
    }

    /**
     * Get Revision History Count.
     */
    public function getRevisionCount(
        int $manuscriptId
    ): int {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )->countAllResults();
    }
}