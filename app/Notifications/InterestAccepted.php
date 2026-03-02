<?php

namespace App\Notifications;

use App\Models\ExchangeInterest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class InterestAccepted extends Notification implements ShouldQueue
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
            'type' => 'interest_accepted',
            'exchange_request_id' => $request->id,
            'interest_id' => $this->interest->id,
            'from_currency' => $request->from_currency,
            'to_currency' => $request->to_currency,
        ];
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        $request = $this->interest->exchangeRequest;
        $listerName = $request->is_anonymous ? 'a community member' : $request->user->name;

        return WebPushMessage::create()
            ->title('Your interest was accepted!')
            ->body("Your exchange request with {$listerName} for {$request->from_currency} → {$request->to_currency} is confirmed")
            ->icon('/apple-touch-icon.png')
            ->data(['url' => route('exchange.my-requests')]);
    }
}
