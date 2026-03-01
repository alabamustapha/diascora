<?php

use App\Livewire\Exchange;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::livewire('/exchange', Exchange\Board::class)->name('exchange.board');
    Route::livewire('/exchange/my-requests', Exchange\MyRequests::class)->name('exchange.my-requests');
});
