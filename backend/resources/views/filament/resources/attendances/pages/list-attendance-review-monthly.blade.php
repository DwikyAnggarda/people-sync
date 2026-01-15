<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Filter Rentang Tanggal
            </x-slot>
            {{ $this->form }}
        </x-filament::section>

        {{-- Overall Statistics --}}
        @php
            $overallStats = $this->getOverallStatistics();
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $overallStats['total_employees'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Karyawan</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $overallStats['total_working_days'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Hari Kerja</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-success-600">{{ $overallStats['total_present'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Hadir</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-danger-600">{{ $overallStats['total_absent'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Tidak Hadir</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-info-600">{{ $overallStats['total_leave'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Cuti/Izin</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $overallStats['total_late'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Terlambat</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-amber-600">{{ $overallStats['total_early_leave'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Pulang Awal</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Legend --}}
        <x-filament::section>
            <x-slot name="heading">
                Keterangan
            </x-slot>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded bg-success-500 flex items-center justify-center text-white text-xs font-bold">H</span>
                    <span class="text-sm">Hadir</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded bg-danger-500 flex items-center justify-center text-white text-xs font-bold">A</span>
                    <span class="text-sm">Tidak Hadir</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded bg-info-500 flex items-center justify-center text-white text-xs font-bold">C</span>
                    <span class="text-sm">Cuti/Izin</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 text-xs font-bold">W</span>
                    <span class="text-sm">Akhir Pekan</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded bg-warning-500 flex items-center justify-center text-white text-xs font-bold">L</span>
                    <span class="text-sm">Libur</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 text-xs font-bold">-</span>
                    <span class="text-sm">Belum Tiba</span>
                </div>
            </div>
        </x-filament::section>

        {{-- Monthly Attendance Table --}}
        @php
            $monthlyData = $this->getMonthlyData();
            $days = $monthlyData['days'];
            $employees = $monthlyData['employees'];
            $dailyTotals = $monthlyData['daily_totals'];
        @endphp
        <x-filament::section>
            <x-slot name="heading">
                Tabel Kehadiran Bulanan
            </x-slot>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-800 z-10">
                                Karyawan
                            </th>
                            @foreach ($days as $day)
                                <th class="px-1 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[32px]" title="{{ $day->translatedFormat('l, d F Y') }}">
                                    <div>{{ $day->format('d') }}</div>
                                    <div class="text-[10px] normal-case">{{ $day->translatedFormat('D') }}</div>
                                </th>
                            @endforeach
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-gray-100 dark:bg-gray-700">
                                Ringkasan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($employees as $employeeData)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-900 z-10">
                                    <div>{{ $employeeData['employee']->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $employeeData['employee']->employee_number }}</div>
                                </td>
                                @foreach ($days as $day)
                                    @php
                                        $dateKey = $day->format('Y-m-d');
                                        $status = $employeeData['daily_statuses'][$dateKey];
                                        $bgColor = $this->getStatusColor($status);
                                        $textColor = $this->getStatusTextColor($status);
                                    @endphp
                                    <td class="px-1 py-2 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $bgColor }} {{ $textColor }} text-xs font-bold" title="{{ $status->label() }}">
                                            {{ $status->shortLabel() }}
                                        </span>
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center text-xs bg-gray-50 dark:bg-gray-800">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-success-600">H: {{ $employeeData['statistics']['present'] }}</span>
                                        <span class="text-danger-600">A: {{ $employeeData['statistics']['absent'] }}</span>
                                        <span class="text-info-600">C: {{ $employeeData['statistics']['leave'] }}</span>
                                        <span class="text-orange-600">T: {{ $employeeData['statistics']['late'] }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Daily Totals Row --}}
                        <tr class="bg-gray-100 dark:bg-gray-700 font-medium">
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white sticky left-0 bg-gray-100 dark:bg-gray-700 z-10">
                                Total Harian
                            </td>
                            @foreach ($days as $day)
                                @php
                                    $dateKey = $day->format('Y-m-d');
                                    $totals = $dailyTotals[$dateKey] ?? ['present' => 0, 'absent' => 0, 'leave' => 0];
                                @endphp
                                <td class="px-1 py-2 text-center text-xs">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-success-600">{{ $totals['present'] }}</span>
                                    </div>
                                </td>
                            @endforeach
                            <td class="px-3 py-2 text-center text-xs bg-gray-100 dark:bg-gray-700">
                                -
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
