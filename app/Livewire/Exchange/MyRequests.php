<?php

namespace App\Livewire\Exchange;

use App\Models\ExchangeInterest;
use App\Models\ExchangeRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Exchange Requests')]
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
            ->exchangeRequests()
            ->with(['interests', 'acceptedInterest.user'])
            ->when($this->search, function ($query): void {
                $query->where(function ($q): void {
                    $q->where('from_currency', 'like', "%{$this->search}%")
                        ->orWhere('to_currency', 'like', "%{$this->search}%");
                });
            })
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
    public function selectedRequest(): ?ExchangeRequest
    {
        if (! $this->selectedRequestId) {
            return null;
        }

        return Auth::user()
            ->exchangeRequests()
            ->with(['interests.user', 'acceptedInterest.user'])
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

    public function acceptInterest(int $interestId): void
    {
        $interest = ExchangeInterest::with('exchangeRequest')->findOrFail($interestId);
        $request = $interest->exchangeRequest;

        if (! $request->isOwnedBy(Auth::user())) {
            return;
        }

        if ($request->status !== 'open') {
            return;
        }

        DB::transaction(function () use ($interest, $request): void {
            $request->interests()
                ->where('id', '!=', $interest->id)
                ->update(['status' => 'rejected']);

            $interest->update(['status' => 'accepted']);

            $request->update([
                'status' => 'matched',
                'accepted_interest_id' => $interest->id,
            ]);
        });

        unset($this->myRequests, $this->selectedRequest);
    }

    public function deleteRequest(int $id): void
    {
        $request = ExchangeRequest::withoutGlobalScopes()->findOrFail($id);

        if (! $request->isOwnedBy(Auth::user())) {
            return;
        }

        if ($this->displayStatus($request) !== 'expired') {
            return;
        }

        if ($request->interests()->exists()) {
            return;
        }

        $request->delete();

        $this->showDetailsModal = false;
        $this->selectedRequestId = null;
        unset($this->myRequests);
    }

    public function closeRequest(int $id): void
    {
        $request = ExchangeRequest::findOrFail($id);

        if (! $request->isOwnedBy(Auth::user())) {
            return;
        }

        $request->update(['status' => 'closed']);
        unset($this->myRequests, $this->selectedRequest);
    }

    public function relistRequest(int $id): void
    {
        $original = ExchangeRequest::findOrFail($id);

        if (! $original->isOwnedBy(Auth::user())) {
            return;
        }

        $expiresAt = null;
        if ($original->expires_at) {
            $durationSeconds = (int) $original->created_at->diffInSeconds($original->expires_at);
            $expiresAt = now()->addSeconds($durationSeconds);
        }

        ExchangeRequest::create([
            'user_id' => Auth::id(),
            'is_anonymous' => $original->is_anonymous,
            'from_currency' => $original->from_currency,
            'to_currency' => $original->to_currency,
            'from_amount' => $original->from_amount,
            'to_amount' => $original->to_amount,
            'official_rate_at_posting' => $original->official_rate_at_posting,
            'offered_rate' => $original->offered_rate,
            'payment_method_sending' => $original->payment_method_sending,
            'payment_method_receiving' => $original->payment_method_receiving,
            'notes' => $original->notes,
            'status' => 'open',
            'expires_at' => $expiresAt,
        ]);

        $this->showDetailsModal = false;
        $this->selectedRequestId = null;
        unset($this->myRequests);
    }

    public function canSeeContact(ExchangeRequest $request): bool
    {
        if ($request->status !== 'matched') {
            return false;
        }

        $userId = Auth::id();

        return $userId === $request->user_id
            || $userId === optional($request->acceptedInterest)->user_id;
    }

    public function displayStatus(ExchangeRequest $request): string
    {
        if ($request->status === 'open' && $request->expires_at?->isPast()) {
            return 'expired';
        }

        return $request->status;
    }

    public function render()
    {
        return view('livewire.exchange.my-requests');
    }
}
