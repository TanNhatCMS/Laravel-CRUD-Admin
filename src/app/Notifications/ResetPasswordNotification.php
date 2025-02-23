<?php

namespace Backpack\CRUD\app\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  $notifiable
     * @param  null  $email
     * @return MailMessage
     */
    public function toMail($notifiable, $email = null): MailMessage
    {
        $email = $email ?? $notifiable->getEmailForPasswordReset();

        return (new MailMessage())
            ->subject(trans('backpack::base.password_reset.subject'))
            ->greeting(trans('backpack::base.password_reset.greeting'))
            ->line([
                trans('backpack::base.password_reset.line_1'),
                trans('backpack::base.password_reset.line_2'),
            ])
            ->action(trans('backpack::base.password_reset.button'), route('backpack.auth.password.reset.token', $this->token).'?email='.urlencode($email))
            ->line(trans('backpack::base.password_reset.notice'));
    }
}
