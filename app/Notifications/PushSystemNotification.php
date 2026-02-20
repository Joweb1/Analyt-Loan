<?php

namespace App\Notifications;

use App\Models\SystemNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PushSystemNotification extends Notification
{
    use Queueable;

    public $systemNotification;

    /**
     * Create a new notification instance.
     */
    public function __construct(SystemNotification $systemNotification)
    {
        $this->systemNotification = $systemNotification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the WebPush representation of the notification.
     */
    public function toWebPush($notifiable, $notification)
    {
        $org = $this->systemNotification->organization;
        $logo = $org && $org->logo_path ? Storage::url($org->logo_path) : 'https://ui-avatars.com/api/?name=A&background=0f1729&color=fff';

        $actorName = $this->systemNotification->user->name ?? 'System';
        $message = $this->systemNotification->message;

        // Determine destination URL
        $url = $this->systemNotification->action_link;
        if (! $url) {
            $url = $notifiable->hasRole('Borrower')
                ? route('borrower.alerts')
                : route('notifications');
        }

        return (new WebPushMessage)
            ->title($this->systemNotification->title)
            ->icon($logo)
            ->body($message)
            ->action('View Details', 'view_action')
            ->data(['url' => $url]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->systemNotification->id,
            'title' => $this->systemNotification->title,
            'message' => $this->systemNotification->message,
        ];
    }
}
