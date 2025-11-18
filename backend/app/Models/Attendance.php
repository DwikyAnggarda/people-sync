<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'employee_id',
        'clock_in',
        'clock_out',
        'clock_in_client_ts',
        'clock_out_client_ts',
        'lat',
        'lng',
        'location',
        'location_accuracy',
        'device_id',
        'photo_url',
        'source',
        'sync_status',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'clock_in_client_ts' => 'datetime',
        'clock_out_client_ts' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'location_accuracy' => 'decimal:3',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
