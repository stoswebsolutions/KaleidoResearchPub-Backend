<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\EmailLogModel;
use App\Models\EmailSettingModel;
use App\Models\EmailTemplateModel;
use CodeIgniter\Email\Email;
use Config\Services;
use Throwable;

class MailService
{
    protected Email $email;

    protected EmailTemplateModel $emailTemplateModel;

    protected EmailLogModel $emailLogModel;

    protected EmailSettingModel $emailSettingModel;

    public function __construct()
    {
        $this->email =
            Services::email();

        $this->emailTemplateModel =
            new EmailTemplateModel();

        $this->emailLogModel =
            new EmailLogModel();

        $this->emailSettingModel =
            new EmailSettingModel();
    }

    /**
     * Initialize SMTP Settings
     */
    protected function initializeEmail(): void
    {
        $setting =
            $this->emailSettingModel
                ->getDefault();

        if (! $setting) {

            throw new \RuntimeException(
                'No active email configuration found.'
            );
        }

        $config = [

            'protocol' =>
                $setting['mail_driver'],

            'SMTPHost' =>
                $setting['smtp_host'],

            'SMTPPort' =>
                (int) $setting['smtp_port'],

            'SMTPUser' =>
                $setting['smtp_user'],

            'SMTPPass' =>
                $setting['smtp_pass'],

            'SMTPCrypto' =>
                $setting['smtp_crypto'],

            'mailType' =>
                'html',

            'charset' =>
                'UTF-8',

            'newline' =>
                "\r\n",

            'CRLF' =>
                "\r\n",
        ];

        $this->email =
            Services::email();

        $this->email->initialize(
            $config
        );

        $this->email->setFrom(
            $setting['from_email'],
            $setting['from_name']
        );

        if (
            ! empty(
                $setting['reply_to_email']
            )
        ) {

            $this->email->setReplyTo(
                $setting['reply_to_email'],
                $setting['reply_to_name']
            );
        }
    }

    /**
     * Send Email
     */
    public function send(
        string $to,
        string $subject,
        string $message,
        array $cc = [],
        array $bcc = [],
        array $attachments = []
    ): bool {

        try {

            $this->email->clear(true);

            $this->initializeEmail();

            $this->email->setTo(
                $to
            );

            if (! empty($cc)) {

                $this->email->setCC(
                    $cc
                );
            }

            if (! empty($bcc)) {

                $this->email->setBCC(
                    $bcc
                );
            }

            foreach (
                $attachments
                as $attachment
            ) {

                if (
                    is_file(
                        $attachment
                    )
                ) {

                    $this->email->attach(
                        $attachment
                    );
                }
            }

            $this->email->setSubject(
                $subject
            );

            $this->email->setMessage(
                $message
            );

            $sent = $this->email->send();

            if (! $sent) {
                log_message(
                    'error',
                    $this->email->printDebugger([
                        'headers',
                        'subject',
                        'body',
                    ])
                );
            }
            return $sent;

        } catch (Throwable $e) {

            log_message(
                'error',
                'MailService: '
                . $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Send HTML Email
     */
    public function sendHtml(
        string $to,
        string $subject,
        string $html,
        array $cc = [],
        array $bcc = [],
        array $attachments = []
    ): bool {

        return $this->send(
            $to,
            $subject,
            $html,
            $cc,
            $bcc,
            $attachments
        );
    }

    /**
     * Log Email
     */
    protected function logEmail(
        ?int $emailTemplateId,
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $message,
        string $status,
        ?string $errorMessage = null
    ): void {

        $this->emailLogModel->insert([

            'email_template_id' =>
                $emailTemplateId,

            'recipient_email' =>
                $recipientEmail,

            'recipient_name' =>
                $recipientName,

            'subject' =>
                $subject,

            'message' =>
                $message,

            'status' =>
                $status,

            'error_message' =>
                $errorMessage,

            'sent_at' =>
                $status === 'sent'
                ? date(
                    'Y-m-d H:i:s'
                )
                : null,
        ]);
    }

    /**
     * Send Template Email
     */
    public function sendTemplate(
        string $templateKey,
        string $to,
        array $variables = [],
        ?string $recipientName = null,
        array $attachments = [],
        array $cc = [],
        array $bcc = []
    ): bool {

        try {

            $template =
                $this->emailTemplateModel
                    ->findByTemplateKey(
                        $templateKey
                    );

            if (
                ! $template
            ) {

                $this->logEmail(
                    null,
                    $to,
                    $recipientName,
                    '',
                    '',
                    'failed',
                    'Template not found: '
                    . $templateKey
                );

                return false;
            }

            if (
                $template['status']
                !== 'active'
            ) {

                return false;
            }

            $subject =
                (string)
                $template['subject'];

            $content =
                (string)
                $template['content'];

            foreach (
                $variables
                as $key => $value
            ) {

                $placeholder =
                    '{{'
                    . $key
                    . '}}';

                $subject =
                    str_replace(
                        $placeholder,
                        (string) $value,
                        $subject
                    );

                $content =
                    str_replace(
                        $placeholder,
                        (string) $value,
                        $content
                    );
            }

            $sent =
                $this->sendHtml(
                    $to,
                    $subject,
                    $content,
                    $cc,
                    $bcc,
                    $attachments
                );

            $this->logEmail(
                (int) $template['id'],
                $to,
                $recipientName,
                $subject,
                $content,
                $sent
                    ? 'sent'
                    : 'failed',
                $sent
                    ? null
                    : 'Email send returned false.'
            );

            return $sent;

        } catch (Throwable $e) {

            log_message(
                'error',
                'MailService: '
                . $e->getMessage()
            );

            $this->logEmail(
                null,
                $to,
                $recipientName,
                '',
                '',
                'failed',
                $e->getMessage()
            );

            return false;
        }
    }
}