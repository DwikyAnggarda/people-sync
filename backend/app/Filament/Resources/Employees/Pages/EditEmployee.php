<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(fn ($record) => $record->user?->delete()),
            ForceDeleteAction::make()
                ->after(fn ($record) => $record->user?->forceDelete()),
            RestoreAction::make()
                ->after(fn ($record) => $record->user?->restore()),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // Update User
            /** @var User $user */
            $user = $record->user;

            if ($user) {
                $userUpdateData = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                ];

                if (filled($data['password'] ?? null)) {
                    $userUpdateData['password'] = Hash::make($data['password']);
                }

                $user->update($userUpdateData);

                if (isset($data['role'])) {
                    $user->syncRoles([$data['role']]);
                }
            }

            // Update Employee
            $employeeData = Arr::except($data, ['password', 'role']);
            $record->update($employeeData);

            return $record;
        });
    }
}
