<?php

namespace App\Http\Resources;

use App\Enums\OperationStatus;
use App\Enums\OperationType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = $this->type instanceof OperationType
            ? $this->type
            : OperationType::from($this->type);

        $status = $this->status instanceof OperationStatus
            ? $this->status
            : OperationStatus::from($this->status);

        return [
            'id' => $this->id,
            'type' => $type->value,
            'type_label' => $type->label(),
            'status' => $status->value,
            'status_label' => $status->label(),
            'amount' => $this->amount,
            'amount_formatted' => sprintf('%.2f', (float) $this->amount),
            'description' => $this->description,
            'balance_before' => $this->balance_before,
            'balance_before_formatted' => $this->balance_before === null ? null : sprintf('%.2f', (float) $this->balance_before),
            'balance_after' => $this->balance_after,
            'balance_after_formatted' => $this->balance_after === null ? null : sprintf('%.2f', (float) $this->balance_after),
            'failure_reason' => $this->failure_reason,
            'date' => $this->created_at?->toAtomString(),
            'processed_at' => $this->processed_at?->toAtomString(),
        ];
    }
}
