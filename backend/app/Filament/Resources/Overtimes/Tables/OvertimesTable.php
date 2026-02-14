<?php

namespace App\Filament\Resources\Overtimes\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OvertimesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Duration')
                    ->state(function ($record) {
                        if (!$record->start_time || !$record->end_time)
                            return '-';
                        try {
                            $start = $record->start_time;
                            $end = $record->end_time;

                            if (!($start instanceof Carbon))
                                $start = Carbon::parse($start);
                            if (!($end instanceof Carbon))
                                $end = Carbon::parse($end);

                            // Clone to avoid modifying original model attributes if they are mutable
                            $start = $start->copy();
                            $end = $end->copy();

                            if ($end->lt($start)) {
                                $end->addDay();
                            }
                            return $end->diff($start)->format('%Hh %Im');
                        } catch (\Exception $e) {
                            return '-';
                        }
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->formatStateUsing(
                        fn(?string $state, $record): string =>
                        $record?->status === 'approved' && $state ? $state : '-'
                    ),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Filter::make('date')
                    ->form([
                        DatePicker::make('date_from'),
                        DatePicker::make('date_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->deferLoading()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25]);
    }
}
