<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $statePath = $getStatePath();
        $state = $getState() ?? [];
        $defaultLat = is_array($state) ? ($state['latitude'] ?? $getDefaultLatitude()) : $getDefaultLatitude();
        $defaultLng = is_array($state) ? ($state['longitude'] ?? $getDefaultLongitude()) : $getDefaultLongitude();
        $defaultRadius = is_array($state) ? ($state['radius_meters'] ?? $getDefaultRadius()) : $getDefaultRadius();
        $minRadius = $getMinRadius();
        $maxRadius = $getMaxRadius();
        $defaultZoom = $getDefaultZoom();
        $mapId = 'map-' . Str::random(8);
    @endphp

    {{-- Leaflet Assets - Only load once --}}
    @once
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <style>
                .leaflet-container { font-family: inherit; }
                .leaflet-control-attribution { font-size: 10px; }
            </style>
        @endpush
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        @endpush
    @endonce

    <div
        wire:ignore
        x-data="mapPickerComponent(@js([
            'statePath' => $statePath,
            'defaultLat' => $defaultLat,
            'defaultLng' => $defaultLng,
            'defaultRadius' => $defaultRadius,
            'minRadius' => $minRadius,
            'maxRadius' => $maxRadius,
            'defaultZoom' => $defaultZoom,
            'mapId' => $mapId,
        ]))"
        x-init="initComponent()"
        class="space-y-4"
    >
        {{-- Search Box --}}
        <div class="relative">
            <input
                type="text"
                x-model="searchQuery"
                @keydown.enter.prevent="searchLocation()"
                @input.debounce.500ms="searchLocation()"
                placeholder="Cari alamat (misal: Monas)..."
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
            />
            <button
                type="button"
                @click="searchLocation()"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary-500"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>

            {{-- Search Results Dropdown --}}
            <div
                x-show="searchResults.length > 0"
                x-cloak
                @click.outside="searchResults = []"
                class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto dark:bg-gray-800 dark:border-gray-700"
            >
                <template x-for="result in searchResults" :key="result.place_id">
                    <div
                        @click="selectResult(result)"
                        class="px-4 py-2 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-200"
                        x-text="result.display_name"
                    ></div>
                </template>
            </div>
        </div>

        {{-- Map Container --}}
        <div
            x-ref="mapContainer"
            id="{{ $mapId }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
            style="height: 400px; min-height: 400px;"
        >
            <div x-show="!mapReady" class="flex items-center justify-center h-full text-gray-500">
                <svg class="animate-spin h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memuat peta...
            </div>
        </div>

        {{-- Radius Slider --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Radius Lokasi
                </label>
                <span class="text-sm font-semibold text-primary-600" x-text="formatRadius(radius)"></span>
            </div>
            <input
                type="range"
                x-model="radius"
                @input="updateRadius()"
                min="{{ $minRadius }}"
                max="{{ $maxRadius }}"
                step="10"
                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary-600 dark:bg-gray-700"
            />
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>{{ $minRadius }}m</span>
                <span>{{ $maxRadius >= 1000 ? ($maxRadius / 1000) . ' km' : $maxRadius . 'm' }}</span>
            </div>
        </div>

        {{-- Coordinates Display --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-lg bg-gray-100 p-3 dark:bg-gray-800">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Latitude</label>
                <div class="text-sm font-mono text-gray-900 dark:text-gray-100" x-text="lat ? lat.toFixed(6) : '-'"></div>
            </div>
            <div class="rounded-lg bg-gray-100 p-3 dark:bg-gray-800">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Longitude</label>
                <div class="text-sm font-mono text-gray-900 dark:text-gray-100" x-text="lng ? lng.toFixed(6) : '-'"></div>
            </div>
            <div class="rounded-lg bg-gray-100 p-3 dark:bg-gray-800">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Radius</label>
                <div class="text-sm font-mono text-gray-900 dark:text-gray-100" x-text="formatRadius(radius)"></div>
            </div>
        </div>

        {{-- Help Text --}}
        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Klik pada peta atau seret marker untuk mengubah lokasi. Gunakan slider untuk mengatur radius.
        </p>
    </div>

    <script>
        function mapPickerComponent(config) {
            return {
                map: null,
                marker: null,
                circle: null,
                lat: config.defaultLat,
                lng: config.defaultLng,
                radius: config.defaultRadius,
                searchQuery: '',
                searchResults: [],
                mapId: config.mapId,
                statePath: config.statePath,
                defaultZoom: config.defaultZoom,
                mapReady: false,

                initComponent() {
                    // Load state from Livewire
                    const currentState = this.$wire.get(this.statePath);
                    if (currentState && typeof currentState === 'object') {
                        this.lat = parseFloat(currentState.latitude ?? currentState.lat ?? this.lat);
                        this.lng = parseFloat(currentState.longitude ?? currentState.lng ?? this.lng);
                        this.radius = parseInt(currentState.radius_meters ?? currentState.radius ?? this.radius);
                    }

                    // Load Leaflet dynamically if needed
                    this.loadLeaflet().then(() => {
                        this.$nextTick(() => {
                            this.initMap();
                        });
                    });
                },

                loadLeaflet() {
                    return new Promise((resolve) => {
                        // Check if Leaflet CSS is loaded
                        if (!document.querySelector('link[href*="leaflet"]')) {
                            const link = document.createElement('link');
                            link.rel = 'stylesheet';
                            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                            document.head.appendChild(link);
                        }

                        // Check if Leaflet JS is loaded
                        if (typeof L !== 'undefined') {
                            resolve();
                            return;
                        }

                        // Load Leaflet JS
                        const script = document.createElement('script');
                        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        script.onload = () => resolve();
                        script.onerror = () => {
                            console.error('Failed to load Leaflet');
                            resolve();
                        };
                        document.head.appendChild(script);
                    });
                },

                initMap() {
                    if (typeof L === 'undefined') {
                        console.error('Leaflet not loaded');
                        return;
                    }

                    const container = document.getElementById(this.mapId);
                    if (!container) {
                        console.error('Map container not found:', this.mapId);
                        return;
                    }

                    // Clear loading indicator
                    container.innerHTML = '';

                    try {
                        // Create map
                        this.map = L.map(this.mapId, {
                            center: [this.lat, this.lng],
                            zoom: this.defaultZoom,
                            zoomControl: true,
                        });

                        // Add tile layer
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '© OpenStreetMap'
                        }).addTo(this.map);

                        // Add marker
                        this.marker = L.marker([this.lat, this.lng], {
                            draggable: true
                        }).addTo(this.map);

                        // Add circle
                        this.circle = L.circle([this.lat, this.lng], {
                            radius: this.radius,
                            color: '#3b82f6',
                            fillColor: '#3b82f6',
                            fillOpacity: 0.2,
                            weight: 2
                        }).addTo(this.map);

                        // Event handlers
                        this.marker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.setPosition(pos.lat, pos.lng);
                        });

                        this.map.on('click', (e) => {
                            this.setPosition(e.latlng.lat, e.latlng.lng);
                        });

                        // Mark as ready
                        this.mapReady = true;

                        // Sync initial state
                        this.syncState();

                        // Fix size issues
                        setTimeout(() => {
                            this.map.invalidateSize();
                        }, 100);

                    } catch (error) {
                        console.error('Error initializing map:', error);
                    }
                },

                setPosition(lat, lng) {
                    this.lat = lat;
                    this.lng = lng;

                    if (this.marker) {
                        this.marker.setLatLng([lat, lng]);
                    }
                    if (this.circle) {
                        this.circle.setLatLng([lat, lng]);
                    }

                    this.syncState();
                },

                updateRadius() {
                    if (this.circle) {
                        this.circle.setRadius(parseInt(this.radius));
                    }
                    this.syncState();
                },

                syncState() {
                    const state = {
                        latitude: this.lat,
                        longitude: this.lng,
                        radius_meters: parseInt(this.radius)
                    };

                    this.$wire.set(this.statePath, state);
                },

                formatRadius(meters) {
                    const m = parseInt(meters) || 0;
                    return m >= 1000 ? (m / 1000).toFixed(1) + ' km' : m + ' m';
                },

                async searchLocation() {
                    if (!this.searchQuery || this.searchQuery.length < 3) {
                        this.searchResults = [];
                        return;
                    }

                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}&limit=5`
                        );
                        this.searchResults = await response.json();
                    } catch (error) {
                        console.error('Search error:', error);
                        this.searchResults = [];
                    }
                },

                selectResult(result) {
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);

                    this.setPosition(lat, lng);

                    if (this.map) {
                        this.map.setView([lat, lng], 16);
                    }

                    this.searchResults = [];
                    this.searchQuery = result.display_name;
                }
            };
        }
    </script>
</x-dynamic-component>
