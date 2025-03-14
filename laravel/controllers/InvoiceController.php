<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Payment\Data\AccountData;
use Modules\Payment\Data\Company;
use Modules\Payment\Data\CompanyData;
use Modules\Payment\Enums\CurrencyEnum;
use Modules\Payment\Enums\PaymentMethodsEnum;
use Modules\Payment\Http\Requests\PayInvoiceRequest;
use Modules\Payment\Http\Resources\InvoiceResource;
use Modules\Payment\Models\Invoice;
use Modules\Payment\Repositories\InvoiceRepository;
use Modules\Payment\Services\Payment\PaymentManager;

final class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly PaymentManager $paymentManager
    ) {
    }

    public function index(): JsonResponse
    {
        $transactions = $this->invoiceRepository->getIndex();

        return response()->json(InvoiceResource::collection($transactions));
    }

    /**
     * @throws Exception
     */
    public function pay(string $invoiceUlid, PaymentMethodsEnum $method, PayInvoiceRequest $request): mixed
    {
        try {
            $invoice = Invoice::query()->where('ulid', $invoiceUlid)->firstOrFail();

            return $this->paymentManager->handleInvoicePayment($method, $invoice);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    public function getPdf(string $invoiceUlid): mixed
    {
        $invoice = Invoice::query()->where('ulid', $invoiceUlid)->with('items')->firstOrFail();
        $time = Str::snake(now()->format('Y-m-d H:i:s'));

        return Pdf::setOptions(['isRemoteEnabled' => true])
            ->loadView('payment::template.invoice', [
                'invoice' => $invoice,
                'companyData' => CompanyData::default(),
                'company' => Company::current(),
                'customerCompany' => Company::customer(),
                'usdAccount' => AccountData::default(CurrencyEnum::USD),
                'eurAccount' => AccountData::default(CurrencyEnum::EUR),
            ])
            ->download("invoice_{$time}_{$invoice->ulid}.pdf");
    }
}
