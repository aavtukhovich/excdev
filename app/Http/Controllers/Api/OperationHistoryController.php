<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OperationHistoryRequest;
use App\Http\Resources\OperationResource;
use App\Services\BalanceOverviewService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OperationHistoryController extends Controller
{
    public function __invoke(OperationHistoryRequest $request, BalanceOverviewService $service): AnonymousResourceCollection
    {
        return OperationResource::collection(
            $service->operations(
                $request->user(),
                $request->search(),
                $request->sortDirection(),
                $request->perPage(),
            ),
        );
    }
}
