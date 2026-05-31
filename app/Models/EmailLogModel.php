<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class EmailLogModel extends Model
{
    protected $table = 'email_logs';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'email_template_id',

        'recipient_email',

        'recipient_name',

        'subject',

        'message',

        'status',

        'error_message',

        'sent_at',

        'created_by',

        'updated_by',

        'deleted_by',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'email_template_id' => '?integer',

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

        'recipient_email' => [
            'label' => 'Recipient Email',
            'rules' => 'required|valid_email|max_length[255]',
        ],

        'recipient_name' => [
            'label' => 'Recipient Name',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'subject' => [
            'label' => 'Subject',
            'rules' => 'required',
        ],

        'message' => [
            'label' => 'Message',
            'rules' => 'required',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[pending,sent,failed]',
        ],

        'error_message' => [
            'label' => 'Error Message',
            'rules' => 'permit_empty',
        ],

        'sent_at' => [
            'label' => 'Sent At',
            'rules' => 'permit_empty',
        ],
    ];

    protected $validationMessages = [

        'recipient_email' => [

            'required' =>
                'Recipient email is required.',

            'valid_email' =>
                'Please provide a valid email address.',
        ],

        'subject' => [

            'required' =>
                'Subject is required.',
        ],

        'message' => [

            'required' =>
                'Message is required.',
        ],

        'status' => [

            'required' =>
                'Status is required.',

            'in_list' =>
                'Invalid email status.',
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
     * Pending Emails.
     */
    public function pending(): self
    {
        return $this->where(
            'status',
            'pending'
        );
    }

    /**
     * Sent Emails.
     */
    public function sent(): self
    {
        return $this->where(
            'status',
            'sent'
        );
    }

    /**
     * Failed Emails.
     */
    public function failed(): self
    {
        return $this->where(
            'status',
            'failed'
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
     * Get Logs By Template.
     */
    public function getByTemplate(
        int $emailTemplateId
    ): array {

        return $this->where(
            'email_template_id',
            $emailTemplateId
        )
        ->orderBy(
            'created_at',
            'DESC'
        )
        ->findAll();
    }

    /**
     * Get Logs By Email.
     */
    public function getByRecipient(
        string $email
    ): array {

        return $this->where(
            'recipient_email',
            $email
        )
        ->orderBy(
            'created_at',
            'DESC'
        )
        ->findAll();
    }
}