<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\Attendances\AttendanceReviewDailyResource;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListAttendanceReviewDaily extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = AttendanceReviewDailyResource::class;

    protected string $view = 'filament.resources.attendances.pages.list-attendance-review-daily';

    #[Url]
    public ?string $selectedDate = null;

    public ?array $filterData = [];

    public function mount(): void
    {
        $this->selectedDate = $this->selectedDate ?? now()->format('Y-m-d');
        $this->filterData = [
            'date' => $this->selectedDate,
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Review Kehadiran (Harian)';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Pilih Tanggal')
                    ->default(now())
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state) {
                        $this->selectedDate = $state;
                    }),
            ])
            ->statePath('filterData');
    }

    public function table(Table $table): Table
    {
        $date = Carbon::parse($this->selectedDate ?? now());
        $service = app(AttendanceService::class);
        $attendanceData = $service->getDailyAttendanceData($date);

        return $table
            ->query(
                \App\Models\Employee::query()
                    ->where('status', 'active')
                    ->orderBy('name')
            )
            ->columns([
                TextColumn::make('employee_number')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Departemen')
                    ->sortable(),
                TextColumn::make('status_display')
                    ->label('Status')
                    ->badge()
                    ->state(function ($record) use ($service, $date) {
                        $status = $service->calculateStatus($record->id, $date);
                        return $status->label();
                    })
                    ->color(function ($record) use ($service, $date) {
                        $status = $service->calculateStatus($record->id, $date);
                        return $status->color();
                    }),
                TextColumn::make('clock_in')
                    ->label('Jam Masuk')
                    ->state(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        return $attendance?->clock_in_at?->format('H:i') ?? '-';
                    })
                    ->color(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        return $attendance?->is_late ? 'danger' : null;
                    })
                    ->description(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        return $attendance?->is_late ? "Terlambat {$attendance->late_duration_formatted}" : null;
                    }),
                TextColumn::make('clock_out')
                    ->label('Jam Keluar')
                    ->state(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        return $attendance?->clock_out_at?->format('H:i') ?? '-';
                    })
                    ->color(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        return $attendance?->is_early_leave ? 'warning' : null;
                    })
                    ->description(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        return $attendance?->is_early_leave ? "Pulang awal {$attendance->early_leave_duration_formatted}" : null;
                    }),
                TextColumn::make('work_duration')
                    ->label('Durasi Kerja')
                    ->state(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        return $attendance?->work_duration_formatted ?? '-';
                    }),
                TextColumn::make('late_status')
                    ->label('Keterlambatan')
                    ->badge()
                    ->state(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        if (!$attendance)
                            return '-';
                        return $attendance->is_late ? 'Terlambat' : 'Tepat Waktu';
                    })
                    ->color(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        if (!$attendance)
                            return 'gray';
                        return $attendance->is_late ? 'danger' : 'success';
                    }),
                TextColumn::make('leave_type')
                    ->label('Tipe Cuti')
                    ->state(function ($record) use ($service, $date) {
                        $leave = $service->getApprovedLeave($record->id, $date);
                        return $leave?->type ?? '-';
                    }),
                TextColumn::make('source_display')
                    ->label('Sumber')
                    ->badge()
                    ->state(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        return $attendance?->source?->value ?? '-';
                    })
                    ->color(function ($record) use ($date) {
                        $attendance = \App\Models\Attendance::forEmployeeAndDate($record->id, $date);
                        return match ($attendance?->source?->value) {
                            'mobile' => 'info',
                            'manual' => 'warning',
                            default => 'gray',
                        };
                    }),
            ])
            ->paginationPageOptions([10, 25])
            ->defaultPaginationPageOption(10);
    }

    public function getAttendanceStatistics(): array
    {
        $date = Carbon::parse($this->selectedDate ?? now());
        $service = app(AttendanceService::class);
        $data = $service->getDailyAttendanceData($date);

        $stats = [
            'total' => $data->count(),
            'present' => 0,
            'absent' => 0,
            'leave' => 0,
            'weekend' => 0,
            'holiday' => 0,
            'late' => 0,
            'early_leave' => 0,
        ];

        foreach ($data as $item) {
            match ($item['status']) {
                AttendanceStatus::Present => $stats['present']++,
                AttendanceStatus::Absent => $stats['absent']++,
                AttendanceStatus::OnLeave => $stats['leave']++,
                AttendanceStatus::Weekend => $stats['weekend']++,
                AttendanceStatus::Holiday => $stats['holiday']++,
                default => null,
            };

            // Count late and early leave
            if ($item['is_late']) {
                $stats['late']++;
            }
            if ($item['is_early_leave']) {
                $stats['early_leave']++;
            }
        }

        return $stats;
    }
}
