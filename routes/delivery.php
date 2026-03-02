<?php

use App\Livewire\Delivery;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::livewire('/delivery', Delivery\Board::class)->name('delivery.board');
    Route::livewire('/delivery/my-requests', Delivery\MyRequests::class)->name('delivery.my-requests');
});
