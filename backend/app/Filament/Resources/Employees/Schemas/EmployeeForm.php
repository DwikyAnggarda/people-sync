<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Department;
use App\Models\Role;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Account & Role')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignoreRecord: true,
                                modifyRuleUsing: function (Unique $rule, ?\Illuminate\Database\Eloquent\Model $record) {
                                    if ($record && $record->user) {
                                        return $rule->ignore($record->user->id);
                                    }
                                    return $rule;
                                }
                            ),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->default('absensi123'),
                        // Select::make('role')
                        //     ->options(Role::pluck('name', 'name'))
                        //     ->required()
                        //     ->live()
                        //     ->afterStateHydrated(function ($component, $state, $record) {
                        //         if (! $state && $record?->user) {
                        //             $component->state($record->user->roles->first()?->name);
                        //         }
                        //     }),
                    ])
                    ->columns(2),

                Section::make('Employee Information')
                    ->schema([
                        TextInput::make('employee_number')
                            ->label('Employee Number')
                            ->required()
                            ->maxLength(255)
                            // ->visible(fn (Get $get) => $get('role') === 'employee')
                            ->default(fn () => 'EMP-' . str_pad((string)(Department::max('id') + 1), 4, '0', STR_PAD_LEFT))
                            ->unique(
                                table: 'employees',
                                column: 'employee_number',
                                ignoreRecord: true,
                            ),
                    ])
                    // ->visible(fn (Get $get) => $get('role') === 'Employee')
                    ->columns(2),

                Section::make('Work Details')
                    ->schema([
                        Select::make('department_id')
                            ->label('Department')
                            ->options(Department::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                        DatePicker::make('joined_at')
                            ->label('Joined Date')
                            ->native(false)
                            ->default(now()),
                    ])
                    ->columns(3),
            ]);
    }
}

