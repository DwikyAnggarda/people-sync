<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreLeaveRequest;
use App\Http\Resources\Api\V1\LeaveResource;
use App\Models\Leave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    /**
     * Get paginated list of my leaves.
     */
    public function index(Request $request): JsonResponse
    {
        $employee = auth()->user()->employee;

        $perPage = min($request->input('per_page', 15), 50);
        $status = $request->input('status');
        $year = $request->input('year', now()->year);

        $query = Leave::where('employee_id', $employee->id)
            ->whereYear('start_date', $year)
            ->orderBy('created_at', 'desc');

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $leaves = $query->paginate($perPage);

        return $this->successWithPagination(
            LeaveResource::collection($leaves),
            $this->getPaginationMeta($leaves)
        );
    }

    /**
     * Create a new leave request.
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $employee = auth()->user()->employee;

        // Check for overlapping leaves
        $hasOverlap = Leave::where('employee_id', $employee->id)
            ->where('status', '!=', 'rejected')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        if ($hasOverlap) {
            return $this->error('Terdapat pengajuan cuti yang bertabrakan dengan tanggal ini', [
                'start_date' => ['Tanggal bertabrakan dengan pengajuan cuti lain'],
            ], 422);
        }

        $leave = DB::transaction(function () use ($employee, $request) {
            return Leave::create([
                'employee_id' => $employee->id,
                'type' => $request->type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);
        });

        return $this->success(
            new LeaveResource($leave),
            'Pengajuan cuti berhasil dikirim',
            201
        );
    }

    /**
     * Get leave detail.
     */
    public function show(Leave $leave): JsonResponse
    {
        $employee = auth()->user()->employee;

        // Ensure the leave belongs to the employee
        if ($leave->employee_id !== $employee->id) {
            return $this->notFound();
        }

        $leave->load('approver');

        return $this->success(new LeaveResource($leave));
    }

    /**
     * Cancel a pending leave request.
     */
    public function destroy(Leave $leave): JsonResponse
    {
        $employee = auth()->user()->employee;

        // Ensure the leave belongs to the employee
        if ($leave->employee_id !== $employee->id) {
            return $this->notFound();
        }

        // Only pending can be cancelled
        if ($leave->status !== 'pending') {
            return $this->error('Hanya pengajuan dengan status pending yang dapat dibatalkan', null, 422);
        }

        $leave->delete();

        return $this->success(null, 'Pengajuan cuti berhasil dibatalkan');
    }
}
