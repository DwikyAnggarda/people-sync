<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $typeLabels = [
            'annual' => 'Cuti Tahunan',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            'unpaid' => 'Cuti Tanpa Gaji',
        ];

        $statusLabels = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        // Calculate total days
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $typeLabels[$this->type] ?? $this->type,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'total_days' => $totalDays,
            'reason' => $this->reason,
            'status' => $this->status,
            'status_label' => $statusLabels[$this->status] ?? $this->status,
            'approved_by' => $this->whenLoaded('approver', function () {
                return $this->approver ? [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                ] : null;
            }),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
