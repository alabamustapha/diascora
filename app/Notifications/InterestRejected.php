<?php

namespace App\Notifications;

use App\Models\ExchangeInterest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class InterestRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ExchangeInterest $interest)
    {
        $this->afterCommit();
    }

    /** @return array<string> */
    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $request = $this->interest->exchangeRequest;

        return [
            'type' => 'interest_rejected',
            'exchange_request_id' => $request->id,
            'interest_id' => $this->interest->id,
            'from_currency' => $request->from_currency,
            'to_currency' => $request->to_currency,
        ];
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        $request = $this->interest->exchangeRequest;

        return WebPushMessage::create()
            ->title('Exchange request filled')
            ->body("Someone else was selected for the {$request->from_currency} → {$request->to_currency} exchange")
            ->icon('/apple-touch-icon.png')
            ->data(['url' => route('exchange.board')]);
    }
}
