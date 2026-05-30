<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptPaymentModel extends Model
{
    protected $table = 'manuscript_payments';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'payment_amount',

        'payment_reference_no',

        'payment_date',

        'payment_screenshot',

        'author_signature',

        'author_id_proof',

        'payment_status',

        'verification_remarks',

        'verified_by',

        'verified_at',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'manuscript_id' => 'integer',

        'payment_amount' => 'float',

        'verified_by' => '?integer',
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

        'payment_amount' => [
            'label' => 'Payment Amount',
            'rules' => 'required|decimal',
        ],

        'payment_reference_no' => [
            'label' => 'Payment Reference Number',
            'rules' => 'permit_empty|max_length[100]',
        ],

        'payment_screenshot' => [
            'label' => 'Payment Screenshot',
            'rules' => 'required|max_length[255]',
        ],

        'author_signature' => [
            'label' => 'Author Signature',
            'rules' => 'required|max_length[255]',
        ],

        'author_id_proof' => [
            'label' => 'Author ID Proof',
            'rules' => 'required|max_length[255]',
        ],

        'payment_status' => [
            'label' => 'Payment Status',
            'rules' => 'required|in_list[pending,approved,rejected]',
        ],
    ];

    protected $validationMessages = [

        'manuscript_id' => [
            'required' =>
                'Manuscript is required.',
        ],

        'payment_amount' => [
            'required' =>
                'Payment amount is required.',

            'decimal' =>
                'Payment amount must be valid.',
        ],

        'payment_screenshot' => [
            'required' =>
                'Payment screenshot is required.',
        ],

        'author_signature' => [
            'required' =>
                'Author signature is required.',
        ],

        'author_id_proof' => [
            'required' =>
                'Author ID proof is required.',
        ],

        'payment_status' => [
            'required' =>
                'Payment status is required.',

            'in_list' =>
                'Invalid payment status selected.',
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
     * Get Payment By Manuscript.
     */
    public function getByManuscript(
        int $manuscriptId
    ): ?array {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )->first();
    }

    /**
     * Check Payment Exists.
     */
    public function paymentExists(
        int $manuscriptId
    ): bool {

        return $this->where(
            'manuscript_id',
            $manuscriptId
        )->countAllResults() > 0;
    }

    /**
     * Verify Payment.
     */
    public function verifyPayment(
        int $paymentId,
        int $verifiedBy,
        string $status,
        ?string $remarks = null
    ): bool {

        return $this->update(
            $paymentId,
            [
                'payment_status'       => $status,
                'verification_remarks' => $remarks,
                'verified_by'          => $verifiedBy,
                'verified_at'          => date(
                    'Y-m-d H:i:s'
                ),
            ]
        );
    }

    /**
     * Get Pending Payments.
     */
    public function getPendingPayments(): array
    {
        return $this->where(
            'payment_status',
            'pending'
        )
        ->orderBy(
            'created_at',
            'ASC'
        )
        ->findAll();
    }

    /**
     * Get Approved Payments.
     */
    public function getApprovedPayments(): array
    {
        return $this->where(
            'payment_status',
            'approved'
        )
        ->orderBy(
            'verified_at',
            'DESC'
        )
        ->findAll();
    }

    /**
     * Get Rejected Payments.
     */
    public function getRejectedPayments(): array
    {
        return $this->where(
            'payment_status',
            'rejected'
        )
        ->orderBy(
            'verified_at',
            'DESC'
        )
        ->findAll();
    }

    /**
     * Payment Statistics.
     */
    public function getStatistics(): array
    {
        return [

            'pending' => $this
                ->where(
                    'payment_status',
                    'pending'
                )
                ->countAllResults(),

            'approved' => $this
                ->where(
                    'payment_status',
                    'approved'
                )
                ->countAllResults(),

            'rejected' => $this
                ->where(
                    'payment_status',
                    'rejected'
                )
                ->countAllResults(),
        ];
    }
}