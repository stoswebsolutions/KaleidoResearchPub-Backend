<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class GalleryModel extends Model
{
    protected $table            = 'gallery';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [

        'uuid',

        'reference_type',
        'reference_id',

        'media_type',

        'media_path',
        'thumbnail_path',

        'title',
        'description',

        'media_date',

        'sort_order',

        'status',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'reference_id' => '?integer',

        'sort_order' => 'integer',

        'created_by' => '?integer',

        'updated_by' => '?integer',

        'deleted_by' => '?integer',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [

        'reference_type' => [
            'label' => 'Reference Type',
            'rules' => 'permit_empty|in_list[banner,gallery,event,conference,workshop,seminar,announcement,journal,award,other]',
        ],

        'reference_id' => [
            'label' => 'Reference ID',
            'rules' => 'permit_empty|integer',
        ],

        'media_type' => [
            'label' => 'Media Type',
            'rules' => 'required|in_list[image,video,document]',
        ],

        'media_path' => [
            'label' => 'Media Path',
            'rules' => 'required|max_length[500]',
        ],

        'thumbnail_path' => [
            'label' => 'Thumbnail Path',
            'rules' => 'permit_empty|max_length[500]',
        ],

        'title' => [
            'label' => 'Title',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty',
        ],

        'media_date' => [
            'label' => 'Media Date',
            'rules' => 'permit_empty|valid_date[Y-m-d]',
        ],

        'sort_order' => [
            'label' => 'Sort Order',
            'rules' => 'permit_empty|integer',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[active,inactive]',
        ],
    ];

    protected $validationMessages = [

        'media_type' => [
            'required' => 'Media type is required.',
            'in_list'  => 'Invalid media type.',
        ],

        'media_path' => [
            'required' => 'Media file is required.',
        ],

        'title' => [
            'required'   => 'Title is required.',
            'min_length' => 'Title must contain at least 2 characters.',
            'max_length' => 'Title cannot exceed 255 characters.',
        ],

        'media_date' => [
            'valid_date' => 'Invalid media date format.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid status.',
        ],
    ];

    protected $skipValidation       = false;
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
     * Active Records.
     */
    public function active(): self
    {
        return $this->where(
            $this->table . '.status',
            'active'
        );
    }

    /**
     * Ordered Records.
     */
    public function ordered(): self
    {
        return $this->orderBy(
            $this->table . '.sort_order',
            'ASC'
        )->orderBy(
            $this->table . '.created_at',
            'DESC'
        );
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
     * By Reference.
     */
    public function byReference(
        string $referenceType,
        ?int $referenceId = null
    ): self {

        $this->where(
            'reference_type',
            $referenceType
        );

        if ($referenceId !== null) {

            $this->where(
                'reference_id',
                $referenceId
            );
        }

        return $this;
    }

    /**
     * Images Only.
     */
    public function images(): self
    {
        return $this->where(
            'media_type',
            'image'
        );
    }

    /**
     * Videos Only.
     */
    public function videos(): self
    {
        return $this->where(
            'media_type',
            'video'
        );
    }

    /**
     * Documents Only.
     */
    public function documents(): self
    {
        return $this->where(
            'media_type',
            'document'
        );
    }
}