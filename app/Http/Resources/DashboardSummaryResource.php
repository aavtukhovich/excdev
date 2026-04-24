<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'balance' => new UserBalanceResource($this['balance']),
            'recent_operations' => OperationResource::collection($this['recent_operations']),
            'refresh_interval_seconds' => $this['refresh_interval_seconds'],
            'refreshed_at' => $this['refreshed_at']?->toAtomString(),
        ];
    }
}
