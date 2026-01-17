@php
    $statePath = $getStatePath();
    $state = $getState() ?? [];
    $latitude = $state['latitude'] ?? $getDefaultLatitude();
    $longitude = $state['longitude'] ?? $getDefaultLongitude();
    $radiusMeters = $state['radius_meters'] ?? $getDefaultRadius();
    $defaultZoom = $getDefaultZoom();
    $minRadius = $getMinRadius();
    $maxRadius = $getMaxRadius();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        .leaflet-container { font-family: inherit; z-index: 1 !important; }
        .map-picker-search-results { z-index: 9999 !important; }
    </style>

    <div
        x-data="{
            state: $wire.$entangle('{{ $statePath }}'),
            latitude: {{ $latitude }},
            longitude: {{ $longitude }},
            radiusMeters: {{ $radiusMeters }},
            searchQuery: '',
            searchResults: [],
            isSearching: false,
            map: null,
            marker: null,
            circle: null,
            debounceTimer: null,
            leafletLoaded: false,

            init() {
                this.loadLeaflet().then(() => {
                    this.$nextTick(() => {
                        this.initMap();
                    });
                });

                this.$watch('radiusMeters', (value) => {
                    this.updateCircle();
                    this.updateState();
                });
            },

            loadLeaflet() {
                return new Promise((resolve) => {
                    if (typeof L !== 'undefined') {
                        this.leafletLoaded = true;
                        resolve();
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = () => {
                        this.leafletLoaded = true;
                        resolve();
                    };
                    document.head.appendChild(script);
                });
            },

            initMap() {
                if (!this.leafletLoaded) return;

                // Initialize map
                this.map = L.map(this.$refs.map).setView([this.latitude, this.longitude], {{ $defaultZoom }});

                // Add OpenStreetMap tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'
                }).addTo(this.map);

                // Add marker
                this.marker = L.marker([this.latitude, this.longitude], {
                    draggable: true
                }).addTo(this.map);

                // Add circle
                this.circle = L.circle([this.latitude, this.longitude], {
                    radius: this.radiusMeters,
                    color: '#3b82f6',
                    fillColor: '#3b82f6',
                    fillOpacity: 0.2,
                    weight: 2
                }).addTo(this.map);

                // Marker drag event
                this.marker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.latitude = parseFloat(pos.lat.toFixed(8));
                    this.longitude = parseFloat(pos.lng.toFixed(8));
                    this.updateCircle();
                    this.updateState();
                });

                // Map click event
                this.map.on('click', (e) => {
                    this.latitude = parseFloat(e.latlng.lat.toFixed(8));
                    this.longitude = parseFloat(e.latlng.lng.toFixed(8));
                    this.marker.setLatLng(e.latlng);
                    this.updateCircle();
                    this.updateState();
                });

                // Initial state update
                this.updateState();
            },

            updateCircle() {
                if (this.circle) {
                    this.circle.setLatLng([this.latitude, this.longitude]);
                    this.circle.setRadius(this.radiusMeters);
                }
            },

            updateState() {
                this.state = {
                    latitude: this.latitude,
                    longitude: this.longitude,
                    radius_meters: parseInt(this.radiusMeters)
                };
            },

            searchAddress() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    if (this.searchQuery.length < 3) {
                        this.searchResults = [];
                        return;
                    }

                    this.isSearching = true;
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}&limit=5`)
                        .then(response => response.json())
                        .then(data => {
                            this.searchResults = data;
                            this.isSearching = false;
                        })
                        .catch(() => {
                            this.searchResults = [];
                            this.isSearching = false;
                        });
                }, 500);
            },

            selectSearchResult(result) {
                this.latitude = parseFloat(parseFloat(result.lat).toFixed(8));
                this.longitude = parseFloat(parseFloat(result.lon).toFixed(8));
                this.searchQuery = result.display_name;
                this.searchResults = [];

                this.map.setView([this.latitude, this.longitude], 16);
                this.marker.setLatLng([this.latitude, this.longitude]);
                this.updateCircle();
                this.updateState();
            },

            formatRadius(meters) {
                if (meters >= 1000) {
                    return (meters / 1000).toFixed(1) + ' km';
                }
                return meters + ' m';
            }
        }"
        class="space-y-4"
    >
        {{-- Search Box --}}
        <div class="relative">
            <div class="relative">
                <input
                    type="text"
                    x-model="searchQuery"
                    @input="searchAddress()"
                    placeholder="Cari alamat..."
                    class="fi-input block w-full rounded-lg border-none bg-white py-2 pl-10 pr-4 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
                />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-filament::icon
                        icon="heroicon-o-magnifying-glass"
                        class="h-5 w-5 text-gray-400"
                    />
                </div>
                <div x-show="isSearching" class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <x-filament::loading-indicator class="h-5 w-5" />
                </div>
            </div>

            {{-- Search Results --}}
            <div
                x-show="searchResults.length > 0"
                x-cloak
                class="absolute z-50 mt-1 w-full rounded-lg bg-white shadow-lg ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/20"
            >
                <ul class="max-h-60 overflow-auto py-1">
                    <template x-for="result in searchResults" :key="result.place_id">
                        <li
                            @click="selectSearchResult(result)"
                            class="cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                            x-text="result.display_name"
                        ></li>
                    </template>
                </ul>
            </div>
        </div>

        {{-- Map Container --}}
        <div
            x-ref="map"
            class="h-80 w-full rounded-lg border border-gray-200 dark:border-gray-700"
            style="z-index: 1;"
        ></div>

        {{-- Radius Slider --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Radius Lokasi
                </label>
                <span
                    class="text-sm font-semibold text-primary-600"
                    x-text="formatRadius(radiusMeters)"
                ></span>
            </div>
            <input
                type="range"
                x-model="radiusMeters"
                min="{{ $minRadius }}"
                max="{{ $maxRadius }}"
                step="10"
                class="w-full accent-primary-600"
            />
            <div class="flex justify-between text-xs text-gray-500">
                <span>{{ $minRadius }}m</span>
                <span>{{ $maxRadius >= 1000 ? ($maxRadius / 1000) . ' km' : $maxRadius . 'm' }}</span>
            </div>
        </div>

        {{-- Coordinates Display --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Latitude</label>
                <div class="text-sm font-mono text-gray-900 dark:text-gray-100" x-text="latitude"></div>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Longitude</label>
                <div class="text-sm font-mono text-gray-900 dark:text-gray-100" x-text="longitude"></div>
            </div>
        </div>

        {{-- Help Text --}}
        <p class="text-xs text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-information-circle" class="inline-block h-4 w-4 mr-1" />
            Klik pada peta atau seret marker untuk mengubah lokasi. Gunakan slider untuk mengatur radius.
        </p>
    </div>
</x-dynamic-component>
