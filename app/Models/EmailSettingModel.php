<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class EmailSettingModel extends Model
{
    protected $table = 'email_settings';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'mail_driver',

        'smtp_host',

        'smtp_port',

        'smtp_user',

        'smtp_pass',

        'smtp_crypto',

        'from_email',

        'from_name',

        'reply_to_email',

        'reply_to_name',

        'is_default',

        'status',

        'created_by',

        'updated_by',

        'deleted_by',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'smtp_port' => '?integer',

        'is_default' => 'integer',

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

        'mail_driver' => [
            'label' => 'Mail Driver',
            'rules' => 'required|in_list[smtp,mail,sendmail]',
        ],

        'smtp_host' => [
            'label' => 'SMTP Host',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'smtp_port' => [
            'label' => 'SMTP Port',
            'rules' => 'permit_empty|integer',
        ],

        'smtp_user' => [
            'label' => 'SMTP Username',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'smtp_pass' => [
            'label' => 'SMTP Password',
            'rules' => 'permit_empty',
        ],

        'smtp_crypto' => [
            'label' => 'SMTP Encryption',
            'rules' => 'permit_empty|in_list[ssl,tls]',
        ],

        'from_email' => [
            'label' => 'From Email',
            'rules' => 'required|valid_email|max_length[255]',
        ],

        'from_name' => [
            'label' => 'From Name',
            'rules' => 'required|max_length[255]',
        ],

        'reply_to_email' => [
            'label' => 'Reply To Email',
            'rules' => 'permit_empty|valid_email|max_length[255]',
        ],

        'reply_to_name' => [
            'label' => 'Reply To Name',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'is_default' => [
            'label' => 'Default Configuration',
            'rules' => 'required|in_list[0,1]',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[active,inactive]',
        ],
    ];

    protected $validationMessages = [

        'mail_driver' => [
            'required' => 'Mail driver is required.',
            'in_list'  => 'Invalid mail driver.',
        ],

        'from_email' => [
            'required'    => 'From email is required.',
            'valid_email' => 'Please enter a valid email address.',
        ],

        'from_name' => [
            'required' => 'From name is required.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid status.',
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
     * Active Records.
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
     * Get Default SMTP Configuration.
     */
    public function getDefault(): ?array
    {
        return $this->where(
            'is_default',
            1
        )
        ->where(
            'status',
            'active'
        )
        ->first();
    }

    /**
     * Set Default Configuration.
     */
    public function setDefault(
        int $id
    ): bool {

        $this->builder()
            ->set(
                'is_default',
                0
            )
            ->update();

        return $this->update(
            $id,
            [
                'is_default' => 1,
            ]
        );
    }
}