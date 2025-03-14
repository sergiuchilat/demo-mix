<?php

namespace Modules\Payment\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Payment\Filters\DateFromFilter;
use Modules\Payment\Filters\DatetimeFromFilter;
use Modules\Payment\Filters\DatetimeToFilter;
use Modules\Payment\Filters\DateToFilter;
use Modules\Payment\Filters\ValueFromFilter;
use Modules\Payment\Filters\ValueToFilter;
use Modules\Payment\Models\Invoice;
use Modules\Payment\Models\SubscriptionPlan;
use Modules\Payment\Sorts\DecimalSort;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

final class InvoiceRepository
{
    private string $model = Invoice::class;

    public function getIndex(): LengthAwarePaginator
    {
        return QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::custom('total_amount_from', new ValueFromFilter(), 'total_amount'),
                AllowedFilter::custom('total_amount_to', new ValueToFilter(), 'total_amount'),
                AllowedFilter::custom('due_date_from', new DateFromFilter(), 'due_date'),
                AllowedFilter::custom('due_date_to', new DateToFilter(), 'due_date'),
                AllowedFilter::custom('created_at_from', new DatetimeFromFilter(), 'created_at'),
                AllowedFilter::custom('created_at_to', new DatetimeToFilter(), 'created_at'),
            ])
            ->allowedSorts(
                [
                    'id',
                    'created_at',
                    'status',
                    AllowedSort::custom('total_amount', new DecimalSort()),
                    'due_date'
                ]
            )
            ->jsonPaginate();
    }
}
