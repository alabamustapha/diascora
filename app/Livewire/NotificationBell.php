<?php

namespace App\Livewire;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationBell extends Component
{
    /** @return Collection<int, DatabaseNotification> */
    #[Computed]
    public function notifications(): Collection
    {
        return auth()->user()->notifications()->latest()->limit(20)->get();
    }

    #[Computed]
    public function unreadCount(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        unset($this->notifications, $this->unreadCount);
    }

    public function markRead(string $notificationId): void
    {
        auth()->user()->notifications()->findOrFail($notificationId)->markAsRead();

        unset($this->notifications, $this->unreadCount);
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
