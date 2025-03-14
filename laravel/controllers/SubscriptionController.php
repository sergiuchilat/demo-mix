<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Network;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Payment\Enums\InvoiceStatusEnum;
use Modules\Payment\Http\Requests\SubscribeToPlanRequest;
use Modules\Payment\Http\Requests\SubscribeToSubnetRequest;
use Modules\Payment\Http\Resources\SubscriptionResource;
use Modules\Payment\Models\InvoiceItem;
use Modules\Payment\Models\Subscription;
use Modules\Payment\Models\SubscriptionPlan;
use Modules\Payment\Repositories\SubscriptionRepository;
use Modules\Payment\Services\Subscription\SubscriptionManager;

final class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly SubscriptionManager $subscriptionManager,
    ) {
    }

    public function index(): JsonResponse
    {
        $transactions = $this->subscriptionRepository->getIndex();

        return response()->json(SubscriptionResource::collection($transactions));
    }

    public function subscribeToPlan(SubscriptionPlan $plan, SubscribeToPlanRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $this->subscriptionManager->subscribeToService(auth()->user(), $plan, $request->input('months', 12));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json();
    }

    public function getNetworks(): JsonResponse
    {
        return response()->json(Network::all());
    }

    public function subscribeToIpSubnet(SubscribeToSubnetRequest $request): JsonResponse
    {
        $subnet = Network::query()->where('ulid', $request->network_ulid)->firstOrFail();

        $alreadyHasSubscription = InvoiceItem::query()
            ->with('subscription')
            ->whereHas('invoice', function ($query) {
                $query->where('status', '<>', InvoiceStatusEnum::OVERDUE->value);
                $query->where('user_id', auth()->id());
            })
            ->where('service_id', $subnet->id)
            ->where('service_type', Network::class)
            ->exists();

        if ($alreadyHasSubscription) {
            return response()->json(['message' => 'Already has subscription'], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();

        try {
            $this->subscriptionManager->subscribeToService(auth()->user(), $subnet, $request->input('months', 12));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json();
    }
}
