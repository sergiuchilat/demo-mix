<?php

namespace Modules\Payment\Services\Subscription;

use App\Models\User\User;
use Illuminate\Support\Str;
use Modules\Payment\Enums\InvoiceStatusEnum;
use Modules\Payment\Events\InvoiceCreated;
use Modules\Payment\Events\SubscriptionEnded;
use Modules\Payment\Jobs\CancelOverdueSubscriptionsJob;
use Modules\Payment\Jobs\CreateInvoicesForSubscription;
use Modules\Payment\Models\Invoice;
use Modules\Payment\Models\InvoiceItem;
use Modules\Payment\Models\Receipt;
use Modules\Payment\Models\Subscription;
use Modules\Payment\Models\SubscriptionPlan;

final class SubscriptionManager
{
    public function __construct()
    {
    }

    public function createInvoicesForTodayActiveSubscriptions(): void
    {
        $allSubscriptionsGot = Subscription::query()
            ->where('is_active', true)
            ->whereDate('end_date', '>=', now()->format('Y-m-d'))
            ->whereDate('next_invoice_date', '=', now()->format('Y-m-d'));

        foreach ($allSubscriptionsGot->get() as $subscription) {
            dispatch(new CreateInvoicesForSubscription($subscription));
        }
    }

    public
    function finishThatEndsToday(): void
    {
        $subsQuery = Subscription::query()
            ->where('is_active', true)
            ->whereDate('end_date', '=', now()->format('Y-m-d'));

        $subsQuery->update(['is_active' => false]);

        $subsQuery->get()->each(function (Subscription $subscription) {
            SubscriptionEnded::dispatch($subscription);
        });
    }

    /**
     * @throws \Exception
     */
    public function subscribeToService(
        User $user,
        SubscriptionPlan|SubscribableInterface $service,
        int $months
    ): Subscription {
        $service->canSubscribeTo($user->id);

        $price = $service->getSubscriptionPrice();

        $subscription = Subscription::query()->create([
            'service_type' => get_class($service),
            'service_id' => $service->id,
            'is_active' => false,
            'subscription_plan_id' => $service instanceof SubscriptionPlan ? $service->id : null,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths($months)->format('Y-m-d'),
            'next_invoice_date' => now()->addMonth()->format('Y-m-d'),
            'user_id' => $user->id,
        ]);

        $invoice = Invoice::query()->create([
            'ulid' => Str::ulid(),
            'total_amount' => $price,
            'status' => InvoiceStatusEnum::NEW,
            'due_date' => now()->addDays(config('payment.invoice_due_days'))->format('Y-m-d'),
            'user_id' => $user->id,
        ]);

        InvoiceItem::query()->create([
            'service_type' => get_class($service),
            'service_id' => $service->id,
            'description' => $service->getSubscriptionDescription(),
            'quantity' => 1,
            'price' => $price,
            'subscription_id' => $subscription->id,
            'invoice_id' => $invoice->id,
        ]);

        InvoiceCreated::dispatch($invoice);

        return $subscription;
    }

    public function cancelThatHaveOverdueInvoices(): void
    {
        $overdueInvoices = Invoice::query()
            ->where('status', InvoiceStatusEnum::NEW)
            ->where('due_date', '=', now()->format('Y-m-d'))
            ->with('items.subscription');

        $overdueInvoices->chunk(50, function ($invoices) {
            foreach ($invoices as $invoice) {
                dispatch(new CancelOverdueSubscriptionsJob($invoice));
            }
        });
    }
}
