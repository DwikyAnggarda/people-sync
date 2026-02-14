<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\Attendances\AttendanceReviewMonthlyResource;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Url;

class ListAttendanceReviewMonthly extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AttendanceReviewMonthlyResource::class;

    protected string $view = 'filament.resources.attendances.pages.list-attendance-review-monthly';

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    public ?array $filterData = [];

    public function mount(): void
    {
        $this->startDate = $this->startDate ?? now()->startOfMonth()->format('Y-m-d');
        $this->endDate = $this->endDate ?? now()->endOfMonth()->format('Y-m-d');
        $this->filterData = [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Review Kehadiran (Bulanan)';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->default(now()->startOfMonth())
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state) {
                        $this->startDate = $state;
                    }),
                DatePicker::make('end_date')
                    ->label('Tanggal Akhir')
                    ->default(now()->endOfMonth())
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state) {
                        $this->endDate = $state;
                    }),
            ])
            ->columns(2)
            ->statePath('filterData');
    }

    public function getMonthlyData(): array
    {
        $startDate = Carbon::parse($this->startDate ?? now()->startOfMonth());
        $endDate = Carbon::parse($this->endDate ?? now()->endOfMonth());

        $service = app(AttendanceService::class);
        return $service->getMonthlyAttendanceData($startDate, $endDate);
    }

    public function getOverallStatistics(): array
    {
        $data = $this->getMonthlyData();

        $stats = [
            'total_employees' => count($data['employees']),
            'total_present' => 0,
            'total_absent' => 0,
            'total_leave' => 0,
            'total_late' => 0,
            'total_early_leave' => 0,
            'total_working_days' => 0,
        ];

        // Count working days in the range
        foreach ($data['days'] as $day) {
            $service = app(AttendanceService::class);
            if ($service->isWorkingDay($day) && !\App\Models\Holiday::isHoliday($day) && !$day->isAfter(Carbon::today())) {
                $stats['total_working_days']++;
            }
        }

        foreach ($data['employees'] as $employee) {
            $stats['total_present'] += $employee['statistics']['present'];
            $stats['total_absent'] += $employee['statistics']['absent'];
            $stats['total_leave'] += $employee['statistics']['leave'];
            $stats['total_late'] += $employee['statistics']['late'];
            $stats['total_early_leave'] += $employee['statistics']['early_leave'];
        }

        return $stats;
    }

    public function getStatusColor(AttendanceStatus $status): string
    {
        return match ($status) {
            AttendanceStatus::Present => 'bg-success-500',
            AttendanceStatus::Absent => 'bg-danger-500',
            AttendanceStatus::OnLeave => 'bg-info-500',
            AttendanceStatus::Weekend => 'bg-gray-300 dark:bg-gray-600',
            AttendanceStatus::Holiday => 'bg-warning-500',
            AttendanceStatus::NotYet => 'bg-gray-200 dark:bg-gray-700',
        };
    }

    public function getStatusTextColor(AttendanceStatus $status): string
    {
        return match ($status) {
            AttendanceStatus::Present => 'text-white',
            AttendanceStatus::Absent => 'text-white',
            AttendanceStatus::OnLeave => 'text-white',
            AttendanceStatus::Weekend => 'text-gray-600 dark:text-gray-300',
            AttendanceStatus::Holiday => 'text-white',
            AttendanceStatus::NotYet => 'text-gray-500 dark:text-gray-400',
        };
    }
}
