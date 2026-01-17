<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_number')
                    ->label('Employee #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('user_id')
                    ->label('Has Account')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => $record->user_id !== null),
                TextColumn::make('joined_at')
                    ->label('Joined Date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department'),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                SelectFilter::make('has_account')
                    ->label('Has Account')
                    ->options([
                        'yes' => 'Has Account',
                        'no' => 'No Account',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'yes') {
                            $query->whereNotNull('user_id');
                        } elseif ($data['value'] === 'no') {
                            $query->whereNull('user_id');
                        }
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('createAccount')
                    ->label('Create Account')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn ($record) => $record->user_id === null && $record->email !== null)
                    ->requiresConfirmation()
                    ->modalHeading('Create User Account')
                    ->modalDescription(fn ($record) => "Create a user account for {$record->name} with email {$record->email}?")
                    ->modalSubmitActionLabel('Create Account')
                    ->form([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->default('absensi123')
                            ->required()
                            ->helperText('Default password is "absensi123"'),
                    ])
                    ->action(function ($record, array $data) {
                        $employeeRole = Role::where('name', 'employee')->first();
                        
                        if (!$employeeRole) {
                            Notification::make()
                                ->title('Error')
                                ->body('Employee role not found. Please create the "employee" role first.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $user = User::create([
                            'name' => $record->name,
                            'email' => $record->email,
                            'password' => Hash::make($data['password']),
                            'email_verified_at' => now(),
                        ]);

                        $user->roles()->attach($employeeRole->id);

                        $record->update(['user_id' => $user->id]);

                        Notification::make()
                            ->title('Account Created')
                            ->body("User account created successfully for {$record->name}.")
                            ->success()
                            ->send();
                    }),
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn ($record) => $record->user_id !== null)
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password')
                    ->modalDescription(fn ($record) => "Reset password for {$record->name}?")
                    ->modalSubmitActionLabel('Reset Password')
                    ->form([
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->default('absensi123')
                            ->required()
                            ->helperText('Default password is "absensi123"'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->user->update([
                            'password' => Hash::make($data['password']),
                        ]);

                        Notification::make()
                            ->title('Password Reset')
                            ->body("Password has been reset for {$record->name}.")
                            ->success()
                            ->send();
                    }),
                Action::make('removeAccount')
                    ->label('Remove Account')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->visible(fn ($record) => $record->user_id !== null)
                    ->requiresConfirmation()
                    ->modalHeading('Remove User Account')
                    ->modalDescription(fn ($record) => "This will delete the user account for {$record->name}. The employee record will be kept. This action cannot be undone.")
                    ->modalSubmitActionLabel('Remove Account')
                    ->action(function ($record) {
                        $user = $record->user;
                        $record->update(['user_id' => null]);
                        $user->delete();

                        Notification::make()
                            ->title('Account Removed')
                            ->body("User account has been removed for {$record->name}.")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn (Collection $records) => $records->each(fn ($record) => $record->user?->delete())),
                    ForceDeleteBulkAction::make()
                        ->after(fn (Collection $records) => $records->each(fn ($record) => $record->user?->forceDelete())),
                    RestoreBulkAction::make()
                        ->after(fn (Collection $records) => $records->each(fn ($record) => $record->user?->restore())),
                ]),
            ]);
    }
}
