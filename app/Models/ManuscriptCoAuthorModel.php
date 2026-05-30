<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptCoAuthorModel extends Model
{
    protected $table = 'manuscript_co_authors';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'author_name',

        'email',

        'designation',

        'university_name',

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

        'author_name' => [
            'label' => 'Author Name',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'email' => [
            'label' => 'Email',
            'rules' => 'permit_empty|valid_email|max_length[255]',
        ],

        'designation' => [
            'label' => 'Designation',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'university_name' => [
            'label' => 'University Name',
            'rules' => 'permit_empty|max_length[255]',
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

        'author_name' => [
            'required' =>
                'Author name is required.',
            'min_length' =>
                'Author name must be at least 2 characters.',
            'max_length' =>
                'Author name cannot exceed 255 characters.',
        ],

        'email' => [
            'valid_email' =>
                'Please provide a valid email address.',
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
     * Get Manuscript Co Authors.
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
}