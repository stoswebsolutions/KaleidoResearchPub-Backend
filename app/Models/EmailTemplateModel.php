<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class EmailTemplateModel extends Model
{
    protected $table = 'email_templates';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'template_key',

        'template_name',

        'subject',

        'content',

        'variables',

        'status',

        'created_by',

        'updated_by',

        'deleted_by',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'created_by' => '?integer',

        'updated_by' => '?integer',

        'deleted_by' => '?integer',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    protected $validationRules = [

        'template_key' => [
            'label' => 'Template Key',
            'rules' => 'required|max_length[150]',
        ],

        'template_name' => [
            'label' => 'Template Name',
            'rules' => 'required|max_length[255]',
        ],

        'subject' => [
            'label' => 'Subject',
            'rules' => 'required',
        ],

        'content' => [
            'label' => 'Content',
            'rules' => 'required',
        ],

        'variables' => [
            'label' => 'Variables',
            'rules' => 'permit_empty',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[active,inactive]',
        ],
    ];

    protected $validationMessages = [

        'template_key' => [
            'required' =>
                'Template key is required.',
        ],

        'template_name' => [
            'required' =>
                'Template name is required.',
        ],

        'subject' => [
            'required' =>
                'Email subject is required.',
        ],

        'content' => [
            'required' =>
                'Email content is required.',
        ],

        'status' => [
            'required' =>
                'Status is required.',

            'in_list' =>
                'Invalid status selected.',
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
     * Active Templates.
     */
    public function active(): self
    {
        return $this->where(
            'status',
            'active'
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
     * Find By Template Key.
     */
    public function findByTemplateKey(
        string $templateKey
    ): ?array {

        return $this->where(
            'template_key',
            $templateKey
        )
        ->where(
            'status',
            'active'
        )
        ->first();
    }

    /**
     * Get Variables Array.
     */
    public function getVariables(
        int $id
    ): array {

        $template =
            $this->find($id);

        if (
            empty(
                $template['variables']
            )
        ) {

            return [];
        }

        $variables = json_decode(
            (string)
            $template['variables'],
            true
        );

        return is_array(
            $variables
        )
            ? $variables
            : [];
    }
}