<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ContactInvite;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ContactInviteReceived extends Notification
{
    public function __construct(
        private readonly ContactInvite $invite,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [WebPushChannel::class, 'database'];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Приглашение в контакты')
            ->body(($this->invite->sender?->name ?? 'Пользователь') . ' хочет добавить вас в контакты')
            ->icon('/android-chrome-192x192.png')
            ->badge('/favicon-96x96.png')
            ->tag('contact-invite-' . $this->invite->id)
            ->data(['url' => '/contacts']);
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'contact_invite',
            'invite_id' => $this->invite->id,
            'sender_id' => $this->invite->sender_id,
            'sender_name' => $this->invite->sender?->name ?? 'Пользователь',
            'contact_id' => $this->invite->contact_id,
        ];
    }
}
