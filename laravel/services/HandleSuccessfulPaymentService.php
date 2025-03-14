<?php

declare(strict_types=1);

namespace Modules\Payment\Services\Payment;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Payment\Enums\InvoiceStatusEnum;
use Modules\Payment\Events\InvoicePaid;
use Modules\Payment\Events\SubscribedWithSuccess;
use Modules\Payment\Models\Invoice;
use Modules\Payment\Models\InvoiceItem;
use Modules\Payment\Models\Receipt;
use Modules\Payment\Models\Subscription;
use Modules\Payment\Services\Subscription\InvoiceableInterface;
use Modules\Payment\Services\Subscription\SubscribableInterface;

final class HandleSuccessfulPaymentService
{
    public function handle(int $invoiceId): void
    {
        DB::transaction(function () use ($invoiceId) {
            $invoice = Invoice::query()
                ->with('items')
                ->lockForUpdate()
                ->where('status', InvoiceStatusEnum::NEW)
                ->findOrFail($invoiceId);

            /** @var InvoiceItem $invoiceItem */
            $invoiceItem = $invoice->items->first();

            if ($invoiceItem->subscription_id !== null) {
                $this->handleSubscriptionInvoicePaid($invoiceItem->subscription_id);
            }

            $invoice->update(['status' => InvoiceStatusEnum::PAID]);

            $this->handleInvoicePaid($invoice);

            Receipt::query()->create([
                'ulid' => Str::ulid(),
                'invoice_id' => $invoiceId,
                'amount' => $invoice->total_amount,
                'user_id' => $invoice->user_id
            ]);
        });
    }

    private function handleSubscriptionInvoicePaid(int $subscriptionId): void
    {
        $invoicesCount = InvoiceItem::query()->where('subscription_id', $subscriptionId)->count();

        /** @var Subscription $subscription */
        $subscription = Subscription::query()->where(
            'id',
            $subscriptionId
        )->firstOrFail();

        /** @var SubscribableInterface $service */
        $service = resolve($subscription->service_type)->where('id', $subscription->service_id)->first();

        $isFirstInvoiceOfSubscription = $invoicesCount === 1;

        if ($isFirstInvoiceOfSubscription) {
            $subscription->update(['is_active' => true]);

            SubscribedWithSuccess::dispatch($subscription);

            $service->doAfterSubscription($subscription);
        }
    }

    private function handleInvoicePaid(
        Invoice $invoice
    ): void {
        /** @var InvoiceItem $invoiceItems */
        $invoiceItems = $invoice->items()->get();

        /** @var InvoiceItem $item */
        foreach ($invoiceItems as $item) {
            $service = $item->getService();
            if ($service instanceof InvoiceableInterface) {
                $service->doAfterInvoicePayment($invoice, $item);
            }
        }

        InvoicePaid::dispatch($invoice);
    }
}
