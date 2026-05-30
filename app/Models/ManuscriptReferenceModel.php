<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptReferenceModel extends Model
{
    protected $table = 'manuscript_references';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'reference_title',

        'reference_author',

        'reference_description',

        'reference_url',

        'sort_order',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'manuscript_id' => 'integer',

        'sort_order' => 'integer',
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

        'reference_title' => [
            'label' => 'Reference Title',
            'rules' => 'required|min_length[2]',
        ],

        'reference_author' => [
            'label' => 'Reference Author',
            'rules' => 'permit_empty',
        ],

        'reference_description' => [
            'label' => 'Reference Description',
            'rules' => 'permit_empty',
        ],

        'reference_url' => [
            'label' => 'Reference URL',
            'rules' => 'permit_empty|valid_url_strict',
        ],

        'sort_order' => [
            'label' => 'Sort Order',
            'rules' => 'permit_empty|integer',
        ],
    ];

    protected $validationMessages = [

        'manuscript_id' => [
            'required' =>
                'Manuscript is required.',
        ],

        'reference_title' => [
            'required' =>
                'Reference title is required.',

            'min_length' =>
                'Reference title must be at least 2 characters.',
        ],

        'reference_url' => [
            'valid_url_strict' =>
                'Please provide a valid URL.',
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
     * Get References By Manuscript.
     */
    public function getByManuscript(
        int $manuscriptId
    ): array {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )
        ->orderBy(
            'sort_order',
            'ASC'
        )
        ->findAll();
    }

    /**
     * Delete References By Manuscript.
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