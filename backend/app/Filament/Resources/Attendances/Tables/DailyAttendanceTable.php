<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Services\AttendanceService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DailyAttendanceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.department.name')
                    ->label('Departemen')
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('clock_in_at')
                    ->label('Jam Masuk')
                    ->dateTime('H:i')
                    ->sortable()
                    ->description(fn($record) => $record->is_late ? "Terlambat {$record->late_duration_formatted}" : null)
                    ->color(fn($record) => $record->is_late ? 'danger' : null),
                TextColumn::make('clock_out_at')
                    ->label('Jam Keluar')
                    ->dateTime('H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->description(fn($record) => $record->is_early_leave ? "Pulang awal {$record->early_leave_duration_formatted}" : null)
                    ->color(fn($record) => $record->is_early_leave ? 'warning' : null),
                TextColumn::make('work_duration_formatted')
                    ->label('Durasi Kerja'),
                IconColumn::make('is_late')
                    ->label('Terlambat')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
                TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->color(fn(AttendanceSource $state): string => match ($state) {
                        AttendanceSource::Mobile => 'info',
                        AttendanceSource::Manual => 'warning',
                    }),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('Sumber')
                    ->options([
                        'mobile' => 'Mobile',
                        'manual' => 'Manual',
                    ]),
                TernaryFilter::make('is_late')
                    ->label('Status Keterlambatan')
                    ->placeholder('Semua')
                    ->trueLabel('Terlambat')
                    ->falseLabel('Tepat Waktu')
                    ->queries(
                        true: fn($query) => $query->where('is_late', true),
                        false: fn($query) => $query->where('is_late', false),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->deferLoading()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25]);
    }
}
