<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignoreRecord: true,
                            ),
                    ])
                    ->columns(2),
                Section::make('Authentication')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->label('Password')
                            ->maxLength(255)
                            ->helperText(fn (string $operation): string => $operation === 'edit' 
                                ? 'Leave empty to keep current password' 
                                : ''),
                        // Select::make('role')
                        //     ->label('Role')
                        //     ->options(Role::pluck('name', 'name'))
                        //     ->required()
                        //     ->dehydrated(false)
                        //     ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
