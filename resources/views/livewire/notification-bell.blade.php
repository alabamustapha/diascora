<div wire:poll.30s>
    <flux:dropdown position="bottom" align="end">
        <flux:button variant="ghost" square class="relative" aria-label="Notifications">
            <flux:icon name="bell" variant="outline" class="size-5" />

            @if ($this->unreadCount > 0)
                <span class="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white leading-none">
                    {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
                </span>
            @endif
        </flux:button>

        <flux:menu class="w-80" keep-open>
            <div class="flex items-center justify-between px-3 py-2 border-b border-zinc-100 dark:border-zinc-700">
                <flux:heading size="sm">Notifications</flux:heading>

                @if ($this->unreadCount > 0)
                    <flux:button
                        wire:click="markAllRead"
                        variant="ghost"
                        size="xs"
                        class="text-xs"
                    >
                        Mark all read
                    </flux:button>
                @endif
            </div>

            @forelse ($this->notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = $notification->read_at === null;
                    $type = $data['type'] ?? '';

                    $label = match ($type) {
                        'interest_received' => 'New interest',
                        'interest_accepted' => 'Interest accepted',
                        'interest_rejected' => 'Request filled',
                        default => 'Notification',
                    };
                @endphp

                <flux:menu.item
                    wire:click="markRead('{{ $notification->id }}')"
                    wire:key="notification-{{ $notification->id }}"
                    keep-open
                    class="flex items-start gap-2 py-2"
                >
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            @if ($isUnread)
                                <span class="size-2 shrink-0 rounded-full bg-blue-500"></span>
                            @else
                                <span class="size-2 shrink-0"></span>
                            @endif
                            <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-400">{{ $label }}</span>
                        </div>
                        <p class="text-sm text-zinc-800 dark:text-zinc-200 truncate pl-3.5">
                            {{ $data['from_currency'] }} → {{ $data['to_currency'] }}
                        </p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 pl-3.5">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                </flux:menu.item>
            @empty
                <div class="px-3 py-6 text-center">
                    <flux:text class="text-zinc-400">No notifications yet</flux:text>
                </div>
            @endforelse
        </flux:menu>
    </flux:dropdown>
</div>
