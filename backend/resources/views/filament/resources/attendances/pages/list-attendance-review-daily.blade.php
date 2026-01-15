<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Filter Tanggal
            </x-slot>
            {{ $this->form }}
        </x-filament::section>

        {{-- Statistics Cards --}}
        @php
            $stats = $this->getAttendanceStatistics();
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Karyawan</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600">{{ $stats['present'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Hadir</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-danger-600">{{ $stats['absent'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Tidak Hadir</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-info-600">{{ $stats['leave'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Cuti/Izin</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ $stats['late'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Terlambat</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-amber-600">{{ $stats['early_leave'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Pulang Awal</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-600">{{ $stats['weekend'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Akhir Pekan</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-warning-600">{{ $stats['holiday'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Libur</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Attendance Table --}}
        <x-filament::section>
            <x-slot name="heading">
                Data Kehadiran - {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}
            </x-slot>
            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
