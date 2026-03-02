<?php

namespace App\Notifications;

use App\Models\DeliveryOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class DeliveryOfferAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly DeliveryOffer $offer)
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
        $request = $this->offer->deliveryRequest;

        return [
            'type' => 'delivery_offer_accepted',
            'delivery_request_id' => $request->id,
            'offer_id' => $this->offer->id,
            'destination_country' => $request->destination_country,
        ];
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        return WebPushMessage::create()
            ->title('Delivery offer accepted!')
            ->body('Your delivery offer was accepted!')
            ->icon('/apple-touch-icon.png')
            ->data(['url' => route('delivery.my-requests')]);
    }
}
