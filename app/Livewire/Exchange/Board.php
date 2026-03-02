<?php

namespace App\Livewire\Exchange;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Models\ExchangeInterest;
use App\Models\ExchangeRequest;
use App\Notifications\InterestReceived;
use App\Services\ExchangeRateService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Exchange Board')]
class Board extends Component
{
    use WithPagination;

    public string $filterFrom = '';

    public string $filterTo = '';

    public bool $showInterestModal = false;

    public bool $showCreateModal = false;

    public ?int $selectedRequestId = null;

    public string $interestComment = '';

    public string $interestPaymentSending = '';

    public string $interestPaymentReceiving = '';

    /** @var array<string, float> */
    public array $displayedRates = [];

    public function mount(): void
    {
        $this->loadRates();
    }

    #[Computed]
    public function requests(): LengthAwarePaginator
    {
        return ExchangeRequest::query()
            ->open()
            ->with('user', 'interests')
            ->when($this->filterFrom, fn ($q) => $q->where('from_currency', $this->filterFrom))
            ->when($this->filterTo, fn ($q) => $q->where('to_currency', $this->filterTo))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function currencies(): array
    {
        return Currency::options();
    }

    #[Computed]
    public function paymentMethods(): array
    {
        return PaymentMethod::options();
    }

    public function clearFilters(): void
    {
        $this->filterFrom = '';
        $this->filterTo = '';
        $this->resetPage();
    }

    public function updatedFilterFrom(): void
    {
        $this->resetPage();
    }

    public function updatedFilterTo(): void
    {
        $this->resetPage();
    }

    public function loadRates(): void
    {
        $service = app(ExchangeRateService::class);

        foreach (Currency::diasporaCurrencies() as $currency) {
            $rate = $service->getRate($currency, Currency::RWF->value);
            if ($rate !== null) {
                $this->displayedRates[$currency] = $rate;
            }
        }
    }

    public function openInterestModal(int $id): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login');

            return;
        }

        $request = ExchangeRequest::find($id);

        if ($request === null || $request->isOwnedBy(Auth::user())) {
            return;
        }

        $this->selectedRequestId = $id;
        $this->interestComment = '';
        $this->interestPaymentSending = '';
        $this->interestPaymentReceiving = '';
        $this->showInterestModal = true;
    }

    public function submitInterest(): void
    {
        $this->validate([
            'interestComment' => ['required', 'string', 'max:1000'],
            'interestPaymentSending' => ['required', 'string'],
            'interestPaymentReceiving' => ['required', 'string'],
        ]);

        $request = ExchangeRequest::findOrFail($this->selectedRequestId);

        if ($request->isOwnedBy(Auth::user())) {
            return;
        }

        $interest = ExchangeInterest::create([
            'exchange_request_id' => $request->id,
            'user_id' => Auth::id(),
            'comment' => $this->interestComment,
            'payment_method_sending' => $this->interestPaymentSending,
            'payment_method_receiving' => $this->interestPaymentReceiving,
            'status' => 'pending',
        ]);

        $request->load('user');
        $request->user->notify(new InterestReceived($interest));

        $this->showInterestModal = false;
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

    public function render()
    {
        return view('livewire.exchange.board');
    }
}
