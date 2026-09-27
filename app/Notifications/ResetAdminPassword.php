<?php

namespace App\Notifications;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Emails an admin the link to choose a new password. Queued, so a slow or failing mail server
 * never delays the forgot-password page or reveals whether an account exists. The job is
 * encrypted because it holds the reset token.
 */
class ResetAdminPassword extends ResetPassword implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [30, 120];

    /**
     * @param  User  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $siteName = SiteSetting::value('site_name', config('app.name'));
        $data = [
            'name' => $notifiable->name,
            'url' => $this->resetUrl($notifiable),
            'expiresInMinutes' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
        ];

        return (new MailMessage)
            ->from(config('mail.from.address'), $siteName)
            ->subject("Reset your {$siteName} admin password")
            ->view('mail.admin.reset-password', $data)
            ->text('mail.admin.reset-password-text', $data);
    }
}
