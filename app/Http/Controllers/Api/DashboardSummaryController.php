<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DashboardSummaryRequest;
use App\Http\Resources\DashboardSummaryResource;
use App\Services\BalanceOverviewService;

class DashboardSummaryController extends Controller
{
    public function __invoke(DashboardSummaryRequest $request, BalanceOverviewService $service): DashboardSummaryResource
    {
        return new DashboardSummaryResource(
            $service->dashboard($request->user()),
        );
    }
}
