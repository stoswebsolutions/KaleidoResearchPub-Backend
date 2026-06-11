<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AccountDetailsModel extends Model
{
    protected $table            = 'account_details';

    protected $primaryKey       = 'id';

    protected $returnType       = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes   = true;

    protected $protectFields    = true;

    protected $allowedFields = [

        'uuid',

        'account_holder_name',

        'account_number',

        'bank_name',

        'branch_name',
        'branch_address',

        'ifsc_code',

        'account_type',

        'upi_id',
        'qr_code_image',

        'is_primary',

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

        'is_primary' => 'integer',

        'sort_order' => 'integer',

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
        
        'id' => [
            'rules' => 'permit_empty|integer',
        ],

        'account_holder_name' => [
            'label' => 'Account Holder Name',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'account_number' => [
            'label' => 'Account Number',
            'rules' => 'required|min_length[6]|max_length[100]',
        ],

        'bank_name' => [
            'label' => 'Bank Name',
            'rules' => 'required|min_length[2]|max_length[255]',
        ],

        'branch_name' => [
            'label' => 'Branch Name',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'branch_address' => [
            'label' => 'Branch Address',
            'rules' => 'permit_empty',
        ],

        'ifsc_code' => [
            'label' => 'IFSC Code',
            'rules' => 'required|min_length[5]|max_length[20]',
        ],

        'account_type' => [
            'label' => 'Account Type',
            'rules' => 'required|in_list[savings,current]',
        ],

        'upi_id' => [
            'label' => 'UPI ID',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'qr_code_image' => [
            'label' => 'QR Code Image',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'is_primary' => [
            'label' => 'Primary Account',
            'rules' => 'permit_empty|in_list[0,1]',
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

        'account_holder_name' => [
            'required'   =>
                'Account holder name is required.',
            'min_length' =>
                'Account holder name must contain at least 2 characters.',
            'max_length' =>
                'Account holder name cannot exceed 255 characters.',
        ],

        'account_number' => [
            'required'   =>
                'Account number is required.',
            'min_length' =>
                'Account number must contain at least 6 characters.',
            'max_length' =>
                'Account number cannot exceed 100 characters.',
        ],

        'bank_name' => [
            'required'   =>
                'Bank name is required.',
            'min_length' =>
                'Bank name must contain at least 2 characters.',
            'max_length' =>
                'Bank name cannot exceed 255 characters.',
        ],

        'ifsc_code' => [
            'required'   =>
                'IFSC code is required.',
            'min_length' =>
                'IFSC code must contain at least 5 characters.',
            'max_length' =>
                'IFSC code cannot exceed 20 characters.',
        ],

        'account_type' => [
            'required' =>
                'Account type is required.',
            'in_list'  =>
                'Invalid account type selected.',
        ],

        'status' => [
            'required' =>
                'Status is required.',
            'in_list'  =>
                'Invalid status selected.',
        ],
    ];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    protected $beforeInsert = [
        'generateUuid',
        'handlePrimaryAccount',
    ];

    protected $beforeUpdate = [
        'handlePrimaryAccount',
    ];

    /**
     * Generate UUID
     */
    protected function generateUuid(
        array $data
    ): array {

        if (
            empty($data['data']['uuid'])
        ) {

            $data['data']['uuid'] = generate_uuid();
        }

        return $data;
    }

    /**
     * Handle Primary Account
     */
    protected function handlePrimaryAccount(
        array $data
    ): array {

        if (
            isset($data['data']['is_primary'])
            &&
            (int) $data['data']['is_primary'] === 1
        ) {

            $this->builder()
                ->set(
                    'is_primary',
                    0
                )
                ->update();
        }

        return $data;
    }

    /**
     * Active Records
     */
    public function active(): self
    {
        return $this->where(
            'status',
            'active'
        );
    }

    /**
     * Primary Account
     */
    public function primary(): self
    {
        return $this->where(
            'is_primary',
            1
        );
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
}