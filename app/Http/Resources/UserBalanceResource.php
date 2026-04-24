<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'current' => $this->balance,
            'current_formatted' => sprintf('%.2f', (float) $this->balance),
        ];
    }
}
