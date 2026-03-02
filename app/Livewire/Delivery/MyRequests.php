<?php

namespace App\Livewire\Delivery;

use App\Models\DeliveryOffer;
use App\Models\DeliveryRequest;
use App\Notifications\DeliveryOfferAccepted;
use App\Notifications\DeliveryOfferRejected;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Delivery Requests')]
class MyRequests extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public bool $showDetailsModal = false;

    public ?int $selectedRequestId = null;

    #[Computed]
    public function myRequests(): LengthAwarePaginator
    {
        return Auth::user()
            ->deliveryRequests()
            ->with(['offers', 'acceptedOffer.user'])
            ->when($this->search, fn ($query) => $query->where('description', 'like', "%{$this->search}%"))
            ->when($this->filterStatus === 'expired', fn ($q) => $q
                ->where('status', 'open')
                ->where('expires_at', '<', now())
            )
            ->when($this->filterStatus && $this->filterStatus !== 'expired', fn ($q) => $q
                ->where('status', $this->filterStatus)
            )
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function selectedRequest(): ?DeliveryRequest
    {
        if (! $this->selectedRequestId) {
            return null;
        }

        return Auth::user()
            ->deliveryRequests()
            ->with(['offers.user', 'acceptedOffer.user'])
            ->find($this->selectedRequestId);
    }

    public function viewDetails(int $id): void
    {
        $this->selectedRequestId = $id;
        unset($this->selectedRequest);
        $this->showDetailsModal = true;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function acceptOffer(int $offerId): void
    {
        $offer = DeliveryOffer::with('deliveryRequest')->findOrFail($offerId);
        $request = $offer->deliveryRequest;

        if (! $request->isOwnedBy(Auth::user())) {
            return;
        }

        if ($request->status !== 'open') {
            return;
        }

        DB::transaction(function () use ($offer, $request): void {
            $rejectedOffers = $request->offers()
                ->with('user')
                ->where('id', '!=', $offer->id)
                ->get();

            $request->offers()
                ->where('id', '!=', $offer->id)
                ->update(['status' => 'rejected']);

            $offer->update(['status' => 'accepted']);

            $request->update([
                'status' => 'matched',
                'accepted_offer_id' => $offer->id,
            ]);

            $offer->load('user');
            $offer->user->notify(new DeliveryOfferAccepted($offer));

            foreach ($rejectedOffers as $rejected) {
                $rejected->user->notify(new DeliveryOfferRejected($rejected));
            }
        });

        unset($this->myRequests, $this->selectedRequest);
    }

    public function closeRequest(int $id): void
    {
        $request = DeliveryRequest::findOrFail($id);

        if (! $request->isOwnedBy(Auth::user())) {
            return;
        }

        $request->update(['status' => 'closed']);
        unset($this->myRequests, $this->selectedRequest);
    }

    public function deleteRequest(int $id): void
    {
        $request = DeliveryRequest::withoutGlobalScopes()->findOrFail($id);

        if (! $request->isOwnedBy(Auth::user())) {
            return;
        }

        if ($this->displayStatus($request) !== 'expired') {
            return;
        }

        if ($request->offers()->exists()) {
            return;
        }

        $request->delete();

        $this->showDetailsModal = false;
        $this->selectedRequestId = null;
        unset($this->myRequests);
    }

    public function canSeeContact(DeliveryRequest $request): bool
    {
        if ($request->status !== 'matched') {
            return false;
        }

        $userId = Auth::id();

        return $userId === $request->user_id
            || $userId === optional($request->acceptedOffer)->user_id;
    }

    public function displayStatus(DeliveryRequest $request): string
    {
        if ($request->status === 'open' && $request->expires_at?->isPast()) {
            return 'expired';
        }

        return $request->status;
    }

    public function render()
    {
        return view('livewire.delivery.my-requests');
    }
}
