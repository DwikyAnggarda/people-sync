<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'org_name',
        'payroll_cutoff_day',
        'timezone',
    ];

    protected $casts = [
        'payroll_cutoff_day' => 'integer',
    ];
}
