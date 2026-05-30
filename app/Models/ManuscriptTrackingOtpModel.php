<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ManuscriptTrackingOtpModel extends Model
{
    protected $table = 'manuscript_tracking_otps';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useAutoIncrement = true;

    protected $protectFields = true;

    protected $allowedFields = [

        'uuid',

        'manuscript_id',

        'email',

        'otp',

        'expires_at',

        'verified_at',

        'attempt_count',

        'is_used',

        'ip_address',

        'user_agent',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected array $casts = [

        'id' => 'integer',

        'manuscript_id' => 'integer',

        'attempt_count' => 'integer',

        'is_used' => 'boolean',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = '';

    protected $deletedField = '';

    protected $validationRules = [

        'manuscript_id' => [
            'label' => 'Manuscript',
            'rules' => 'required|integer',
        ],

        'email' => [
            'label' => 'Email',
            'rules' => 'required|valid_email|max_length[255]',
        ],

        'otp' => [
            'label' => 'OTP',
            'rules' => 'required|max_length[10]',
        ],

        'expires_at' => [
            'label' => 'Expires At',
            'rules' => 'required',
        ],
    ];

    protected $validationMessages = [

        'manuscript_id' => [
            'required' =>
                'Manuscript is required.',
        ],

        'email' => [
            'required' =>
                'Email is required.',

            'valid_email' =>
                'Please provide a valid email address.',
        ],

        'otp' => [
            'required' =>
                'OTP is required.',
        ],

        'expires_at' => [
            'required' =>
                'OTP expiry time is required.',
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

            $data['data']['uuid']
                = generate_uuid();
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
     * Generate OTP.
     */
    public function generateOtp(
        int $manuscriptId,
        string $email,
        string $ipAddress = '',
        string $userAgent = ''
    ): string {

        $otp = (string) random_int(
            100000,
            999999
        );

        $this->insert([
            'manuscript_id' => $manuscriptId,
            'email'         => $email,
            'otp'           => $otp,
            'expires_at'    => date(
                'Y-m-d H:i:s',
                strtotime('+10 minutes')
            ),
            'attempt_count' => 0,
            'is_used'       => 0,
            'ip_address'    => $ipAddress,
            'user_agent'    => $userAgent,
        ]);

        return $otp;
    }

    /**
     * Verify OTP.
     */
    public function verifyOtp(
        int $manuscriptId,
        string $email,
        string $otp
    ): bool {

        $record = $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'email',
                $email
            )
            ->where(
                'otp',
                $otp
            )
            ->where(
                'is_used',
                0
            )
            ->orderBy(
                'id',
                'DESC'
            )
            ->first();

        if (! $record) {
            return false;
        }

        if (
            strtotime(
                $record['expires_at']
            ) < time()
        ) {
            return false;
        }

        $this->update(
            $record['id'],
            [
                'is_used'     => 1,
                'verified_at' => date(
                    'Y-m-d H:i:s'
                ),
            ]
        );

        return true;
    }

    /**
     * Increment Attempt Count.
     */
    public function incrementAttempts(
        int $id
    ): bool {

        $record = $this->find($id);

        if (! $record) {
            return false;
        }

        return $this->update(
            $id,
            [
                'attempt_count' =>
                    ((int) $record['attempt_count']) + 1,
            ]
        );
    }

    /**
     * Get Latest OTP.
     */
    public function getLatestOtp(
        int $manuscriptId,
        string $email
    ): ?array {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'email',
                $email
            )
            ->orderBy(
                'id',
                'DESC'
            )
            ->first();
    }

    /**
     * Get Active OTP.
     */
    public function getActiveOtp(
        int $manuscriptId,
        string $email
    ): ?array {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'email',
                $email
            )
            ->where(
                'is_used',
                0
            )
            ->where(
                'expires_at >=',
                date('Y-m-d H:i:s')
            )
            ->orderBy(
                'id',
                'DESC'
            )
            ->first();
    }

    /**
     * Expire Previous OTPs.
     */
    public function expireOldOtps(
        int $manuscriptId,
        string $email
    ): bool {

        return $this->where(
                'manuscript_id',
                $manuscriptId
            )
            ->where(
                'email',
                $email
            )
            ->where(
                'is_used',
                0
            )
            ->set([
                'is_used' => 1,
            ])
            ->update();
    }

    /**
     * Cleanup Expired OTPs.
     */
    public function cleanupExpiredOtps(): bool
    {
        return $this->where(
                'expires_at <',
                date('Y-m-d H:i:s')
            )
            ->delete();
    }
}