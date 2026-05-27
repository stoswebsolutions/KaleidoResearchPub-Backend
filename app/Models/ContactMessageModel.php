<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ContactMessageModel extends Model
{
    protected $table            = 'contact_messages';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'uuid',

        'full_name',
        'email',
        'phone',

        'subject',
        'message',

        'message_type',

        'status',

        'ip_address',
        'user_agent',

        'is_read',
        'read_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'full_name' => [
            'label' => 'Full Name',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'email' => [
            'label' => 'Email',
            'rules' => 'required|valid_email|max_length[255]',
        ],

        'phone' => [
            'label' => 'Phone',
            'rules' => 'permit_empty|max_length[50]',
        ],

        'subject' => [
            'label' => 'Subject',
            'rules' => 'required|min_length[3]|max_length[255]',
        ],

        'message' => [
            'label' => 'Message',
            'rules' => 'required|min_length[10]',
        ],

        'message_type' => [
            'label' => 'Message Type',
            'rules' => 'required|in_list[general,author,reviewer,journal,membership,technical,other]',
        ],

        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[new,in_progress,resolved,closed,spam]',
        ],

        'ip_address' => [
            'label' => 'IP Address',
            'rules' => 'permit_empty|max_length[45]',
        ],

        'user_agent' => [
            'label' => 'User Agent',
            'rules' => 'permit_empty',
        ],

        'is_read' => [
            'label' => 'Read Status',
            'rules' => 'permit_empty|in_list[0,1]',
        ],
    ];

    protected $validationMessages = [
        'full_name' => [
            'required'   => 'Full name is required.',
            'min_length' => 'Full name must contain at least 2 characters.',
            'max_length' => 'Full name cannot exceed 255 characters.',
        ],

        'email' => [
            'required'    => 'Email address is required.',
            'valid_email' => 'Please enter a valid email address.',
        ],

        'subject' => [
            'required'   => 'Subject is required.',
            'min_length' => 'Subject must contain at least 3 characters.',
            'max_length' => 'Subject cannot exceed 255 characters.',
        ],

        'message' => [
            'required'   => 'Message is required.',
            'min_length' => 'Message must contain at least 10 characters.',
        ],

        'message_type' => [
            'required' => 'Message type is required.',
            'in_list'  => 'Invalid message type selected.',
        ],

        'status' => [
            'required' => 'Status is required.',
            'in_list'  => 'Invalid message status selected.',
        ],

        'is_read' => [
            'in_list' => 'Invalid read status.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    protected $beforeInsert = [
        'generateUuid',
        'prepareDefaults',
    ];

    /**
     * Auto Generate UUID
     */
    protected function generateUuid(
        array $data
    ): array {
        if (
            empty(
                $data['data']['uuid']
            )
        ) {
            $data['data']['uuid'] = generate_uuid();
        }

        return $data;
    }

    /**
     * Default Values
     */
    protected function prepareDefaults(
        array $data
    ): array {
        $data['data']['message_type'] ??= 'general';
        $data['data']['status']       ??= 'new';
        $data['data']['is_read']      ??= 0;

        return $data;
    }

    /**
     * Find By UUID
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
     * Unread Messages
     */
    public function unread(): self
    {
        return $this->where(
            'is_read',
            0
        );
    }

    /**
     * Read Messages
     */
    public function read(): self
    {
        return $this->where(
            'is_read',
            1
        );
    }

    /**
     * New Messages
     */
    public function newMessages(): self
    {
        return $this->where(
            'status',
            'new'
        );
    }

    /**
     * Mark As Read
     */
    public function markAsRead(
        int $id
    ): bool {
        return $this->update(
            $id,
            [
                'is_read' => 1,
                'read_at' => date(
                    'Y-m-d H:i:s'
                ),
            ]
        );
    }

    /**
     * Mark As Unread
     */
    public function markAsUnread(
        int $id
    ): bool {
        return $this->update(
            $id,
            [
                'is_read' => 0,
                'read_at' => null,
            ]
        );
    }

    /**
     * Mark As Resolved
     */
    public function markAsResolved(
        int $id
    ): bool {
        return $this->update(
            $id,
            [
                'status' => 'resolved',
            ]
        );
    }

    /**
     * Get Unread Count
     */
    public function getUnreadCount(): int
    {
        return $this->where(
            'is_read',
            0
        )->countAllResults();
    }

    /**
     * Get New Count
     */
    public function getNewCount(): int
    {
        return $this->where(
            'status',
            'new'
        )->countAllResults();
    }
}