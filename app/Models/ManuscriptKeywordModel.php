<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptKeywordModel extends Model
{
    protected $table = 'manuscript_keywords';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'keyword',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'manuscript_id' => 'integer',
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

        'keyword' => [
            'label' => 'Keyword',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],
    ];

    protected $validationMessages = [

        'manuscript_id' => [
            'required' =>
                'Manuscript is required.',
        ],

        'keyword' => [
            'required' =>
                'Keyword is required.',

            'min_length' =>
                'Keyword must be at least 2 characters.',

            'max_length' =>
                'Keyword cannot exceed 255 characters.',
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
     * Get Keywords By Manuscript.
     */
    public function getByManuscript(
        int $manuscriptId
    ): array {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )
        ->orderBy(
            'keyword',
            'ASC'
        )
        ->findAll();
    }

    /**
     * Delete All Keywords By Manuscript.
     */
    public function deleteByManuscript(
        int $manuscriptId
    ): bool {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )->delete();
    }
}