<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreOvertimeRequest;
use App\Http\Resources\Api\V1\OvertimeResource;
use App\Models\Overtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OvertimeController extends Controller
{
    /**
     * Get paginated list of my overtimes.
     */
    public function index(Request $request): JsonResponse
    {
        $employee = auth()->user()->employee;

        $perPage = min($request->input('per_page', 15), 50);
        $status = $request->input('status');
        $month = $request->input('month');
        $year = $request->input('year', now()->year);

        $query = Overtime::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->orderBy('created_at', 'desc');

        if ($month) {
            $query->whereMonth('date', $month);
        }

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $overtimes = $query->paginate($perPage);

        return $this->successWithPagination(
            OvertimeResource::collection($overtimes),
            $this->getPaginationMeta($overtimes)
        );
    }

    /**
     * Create a new overtime request.
     */
    public function store(StoreOvertimeRequest $request): JsonResponse
    {
        $employee = auth()->user()->employee;

        // Check for duplicate overtime on same date
        $hasDuplicate = Overtime::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->where('status', '!=', 'rejected')
            ->exists();

        if ($hasDuplicate) {
            return $this->error('Anda sudah memiliki pengajuan lembur pada tanggal ini', [
                'date' => ['Tanggal sudah ada pengajuan lembur lain'],
            ], 422);
        }

        $overtime = DB::transaction(function () use ($employee, $request) {
            return Overtime::create([
                'employee_id' => $employee->id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);
        });

        return $this->success(
            new OvertimeResource($overtime),
            'Pengajuan lembur berhasil dikirim',
            201
        );
    }

    /**
     * Get overtime detail.
     */
    public function show(Overtime $overtime): JsonResponse
    {
        $employee = auth()->user()->employee;

        // Ensure the overtime belongs to the employee
        if ($overtime->employee_id !== $employee->id) {
            return $this->notFound();
        }

        $overtime->load('approver');

        return $this->success(new OvertimeResource($overtime));
    }

    /**
     * Cancel a pending overtime request.
     */
    public function destroy(Overtime $overtime): JsonResponse
    {
        $employee = auth()->user()->employee;

        // Ensure the overtime belongs to the employee
        if ($overtime->employee_id !== $employee->id) {
            return $this->notFound();
        }

        // Only pending can be cancelled
        if ($overtime->status !== 'pending') {
            return $this->error('Hanya pengajuan dengan status pending yang dapat dibatalkan', null, 422);
        }

        $overtime->delete();

        return $this->success(null, 'Pengajuan lembur berhasil dibatalkan');
    }
}
