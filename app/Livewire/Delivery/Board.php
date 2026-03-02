<?php

namespace App\Livewire\Delivery;

use App\Enums\DeliveryCountry;
use App\Models\DeliveryOffer;
use App\Models\DeliveryRequest;
use App\Notifications\DeliveryOfferReceived;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Delivery Board')]
class Board extends Component
{
    use WithPagination;

    public string $filterCountry = '';

    public bool $showOfferModal = false;

    public bool $showCreateModal = false;

    public ?int $selectedRequestId = null;

    public string $offerMessage = '';

    #[Computed]
    public function requests(): LengthAwarePaginator
    {
        return DeliveryRequest::query()
            ->open()
            ->with('user', 'offers')
            ->when($this->filterCountry, fn ($q) => $q->where('destination_country', $this->filterCountry))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function countries(): array
    {
        return DeliveryCountry::options();
    }

    public function updatedFilterCountry(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterCountry = '';
        $this->resetPage();
    }

    public function openOfferModal(int $id): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login');

            return;
        }

        $request = DeliveryRequest::find($id);

        if ($request === null || $request->isOwnedBy(Auth::user())) {
            return;
        }

        $this->selectedRequestId = $id;
        $this->offerMessage = '';
        $this->showOfferModal = true;
    }

    public function submitOffer(): void
    {
        $this->validate([
            'offerMessage' => ['required', 'string', 'max:1000'],
        ]);

        $request = DeliveryRequest::findOrFail($this->selectedRequestId);

        if ($request->isOwnedBy(Auth::user())) {
            return;
        }

        $offer = DeliveryOffer::create([
            'delivery_request_id' => $request->id,
            'user_id' => Auth::id(),
            'message' => $this->offerMessage,
            'status' => 'pending',
        ]);

        $request->load('user');
        $request->user->notify(new DeliveryOfferReceived($offer));

        $this->showOfferModal = false;
        $this->selectedRequestId = null;
        unset($this->requests);
    }

    public function openCreateModal(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login');

            return;
        }

        $this->showCreateModal = true;
    }

    #[On('delivery-request-created')]
    public function onRequestCreated(): void
    {
        $this->showCreateModal = false;
        unset($this->requests);
    }

    public function render()
    {
        return view('livewire.delivery.board');
    }
}
