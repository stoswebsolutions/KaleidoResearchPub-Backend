<?php

declare(strict_types=1);

use App\Models\ActivityLogModel;

if (! function_exists('activity_log')) {

    /**
     * Write activity log.
     */
    function activity_log(
        int $profileId,
        string $module,
        string $action,
        ?int $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): bool {

        try {

            $activityLogModel = new ActivityLogModel();

            return $activityLogModel->insert([
                'profile_id' => $profileId,
                'module'     => $module,
                'action'     => $action,
                'record_id'  => $recordId,

                'old_values' => $oldValues === null
                    ? null
                    : json_encode(
                        $oldValues,
                        JSON_UNESCAPED_UNICODE
                    ),

                'new_values' => $newValues === null
                    ? null
                    : json_encode(
                        $newValues,
                        JSON_UNESCAPED_UNICODE
                    ),

                'ip_address' => service('request')
                    ->getIPAddress(),

                'user_agent' => service('request')
                    ->getUserAgent()
                    ->getAgentString(),

                'created_at' => date(
                    'Y-m-d H:i:s'
                ),
            ]) !== false;

        } catch (\Throwable $exception) {

            log_message(
                'error',
                'Activity log failed: {message}',
                [
                    'message' => $exception->getMessage(),
                ]
            );

            return false;
        }
    }
}