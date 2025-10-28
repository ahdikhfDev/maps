{{-- resources/views/maps/index.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyberpunk Interactive Map</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --primary-color: #00f6ff; /* Neon Cyan */
            --secondary-color: #ff00c1; /* Neon Magenta */
            --bg-dark: #0a0c1a;
            --bg-surface: rgba(23, 28, 58, 0.6);
            --bg-surface-solid: #171c3a;
            --text-light: #a0a8d3;
            --text-bright: #e9eaff;
            --border-color: rgba(0, 246, 255, 0.2);
            --border-hover: rgba(0, 246, 255, 0.7);
            --glow-shadow-sm: 0 0 8px rgba(0, 246, 255, 0.2);
            --glow-shadow-md: 0 0 15px rgba(0, 246, 255, 0.3), 0 0 5px rgba(0, 246, 255, 0.5);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            background-image:
                linear-gradient(rgba(0, 246, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 246, 255, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
            color: var(--text-light);
            overflow: hidden;
        }

        .main-wrapper { display: flex; height: 100vh; position: relative; }

        /* === Sidebar === */
        .sidebar {
            width: 420px;
            background: var(--bg-surface);
            backdrop-filter: blur(10px);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1002;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-header { padding: 24px; border-bottom: 1px solid var(--border-color); }
        .sidebar-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 26px;
            font-weight: 700;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 10px rgba(255, 0, 193, 0.5);
        }

        .search-wrapper { position: relative; margin-top: 16px; }
        .search-wrapper svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: var(--text-light); transition: color 0.2s; }
        #search-input {
            width: 100%; padding: 12px 16px 12px 44px; border-radius: 4px;
            border: 1px solid var(--border-color); font-size: 14px;
            transition: all 0.2s; background-color: transparent; color: var(--text-bright);
        }
        #search-input:focus {
            outline: none; border-color: var(--border-hover);
            box-shadow: var(--glow-shadow-md);
            background-color: var(--bg-surface-solid);
        }
        #search-input::placeholder { color: var(--text-light); }
        #search-input:focus + svg { color: var(--primary-color); }

        .filter-section { padding: 16px 24px; border-bottom: 1px solid var(--border-color); }
        .filter-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
        .filter-btn {
            padding: 8px 16px; border: 1px solid var(--border-color);
            background: transparent; color: var(--text-light);
            border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 12px;
            transition: all 0.2s; text-transform: uppercase;
        }
        .filter-btn:hover { border-color: var(--border-hover); color: var(--primary-color); box-shadow: var(--glow-shadow-sm); }
        .filter-btn.active {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: var(--bg-dark); border-color: transparent; font-weight: 700;
            box-shadow: 0 0 15px rgba(255, 0, 193, 0.4);
        }

        .locations-list { flex-grow: 1; overflow-y: auto; padding: 16px 8px 24px 24px; }
        .list-header { font-size: 14px; font-weight: 600; color: var(--text-light); padding: 0 16px 16px 0; text-transform: uppercase; letter-spacing: 1px; }

        .location-card {
            background: transparent;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--border-color);
            border-radius: 4px;
            padding: 16px;
            margin: 0 16px 16px 0;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .location-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--glow-shadow-md);
            border-color: var(--border-hover);
            border-left-color: var(--primary-color);
        }
        .location-card.active {
            border-left: 4px solid var(--primary-color);
            background: rgba(0, 246, 255, 0.05);
            border-color: var(--border-hover);
        }
        .location-card.hidden { display: none; }
        .location-image { width: 100%; height: 160px; object-fit: cover; border-radius: 4px; margin-bottom: 16px; opacity: 0.85; transition: opacity 0.3s ease;}
        .location-card:hover .location-image { opacity: 1; }
        .location-name { font-size: 18px; font-weight: 700; color: var(--text-bright); margin-bottom: 8px; }
        .location-category {
            display: inline-block; padding: 5px 12px;
            background-color: var(--border-color);
            color: var(--primary-color); border-radius: 20px;
            font-size: 11px; font-weight: 700; margin-bottom: 12px; text-transform: uppercase;
        }
        .location-address { color: var(--text-light); font-size: 13px; display: flex; align-items: start; gap: 8px; line-height: 1.5; }
        .location-address svg { margin-top: 3px; min-width: 14px; color: var(--primary-color); }

        #no-results { text-align: center; color: var(--text-light); padding: 50px 20px; display: none; }
        #no-results h3 { font-family: 'Orbitron', sans-serif; color: var(--secondary-color); }

        /* === Map Area === */
        .map-container { flex-grow: 1; position: relative; background: var(--bg-dark); }
        #map { height: 100%; width: 100%; z-index: 1; }
        .leaflet-top.leaflet-right { top: 78px; right: 15px; } /* Pindah zoom control */
        .leaflet-control-zoom-in, .leaflet-control-zoom-out { background-color: var(--bg-surface-solid) !important; border: 1px solid var(--border-color) !important; color: var(--primary-color) !important; border-radius: 4px !important; }
        .leaflet-control-zoom-in:hover, .leaflet-control-zoom-out:hover { background-color: var(--bg-dark) !important; }

        /* === Custom Marker & Popup Style === */
        @keyframes marker-pop { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .custom-marker-pin { animation: marker-pop 0.3s ease-out; }
        .leaflet-popup-content-wrapper, .leaflet-popup-tip { background: var(--bg-surface-solid) !important; border: 1px solid var(--border-color) !important; border-radius: 4px !important; box-shadow: var(--glow-shadow-md) !important; color: var(--text-light) !important; }
        .leaflet-popup-content { margin: 15px !important; font-family: 'Plus Jakarta Sans', sans-serif; }
        .leaflet-popup-close-button { color: var(--text-light) !important; }
        .popup-image { width: 100%; height: 120px; object-fit: cover; border-radius: 4px; margin-bottom: 10px; opacity: 0.9; }
        .popup-title { font-size: 16px; font-weight: 700; margin: 0 0 5px 0; color: var(--text-bright); }
        .popup-category { display: inline-block; padding: 4px 10px; background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); color: var(--bg-dark); border-radius: 10px; font-size: 10px; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; }

        /* === Scrollbar === */
        .locations-list::-webkit-scrollbar { width: 8px; }
        .locations-list::-webkit-scrollbar-track { background: transparent; }
        .locations-list::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; border: 2px solid transparent; }
        .locations-list::-webkit-scrollbar-thumb:hover { background: var(--primary-color); }

        /* === Custom Cyberpunk Attribution Style === */
        .leaflet-control-attribution a,
        .leaflet-control-attribution .cyber-text {
            font-family: 'Orbitron', sans-serif !important;
            font-size: 11px !important;
            color: var(--primary-color) !important;
            text-shadow: 0 0 5px rgba(0, 246, 255, 0.5), 0 0 10px rgba(0, 246, 255, 0.3) !important;
            text-decoration: none !important;
            transition: all 0.2s ease-in-out;
        }
        .leaflet-control-attribution a:hover {
            color: var(--secondary-color) !important;
            text-shadow: 0 0 8px rgba(255, 0, 193, 0.6), 0 0 15px rgba(255, 0, 193, 0.4) !important;
        }
        .leaflet-control-attribution {
            background-color: rgba(23, 28, 58, 0.7) !important;
            padding: 5px 8px !important;
            border-radius: 4px !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: var(--glow-shadow-sm);
        }

        /* === EFEK DENYUT NEON BARU === */
        @keyframes pulse-animation {
            0% { transform: scale(0.8); opacity: 0.5; }
            50% { transform: scale(1.5); opacity: 1; }
            100% { transform: scale(2.2); opacity: 0; }
        }
        .pulsating-halo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 246, 255, 0) 0%, rgba(0, 246, 255, 0.3) 40%, rgba(0, 246, 255, 0) 70%);
            animation: pulse-animation 3s infinite cubic-bezier(0.25, 0.46, 0.45, 0.94);
            pointer-events: none; /* Agar tidak bisa diklik */
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <button id="sidebar-toggle" aria-label="Toggle sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div class="map-overlay"></div>

        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>./Jelajah_Peta</h1>
                <div class="search-wrapper">
                    <input type="text" id="search-input" placeholder="Cari nama lokasi, alamat...">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                </div>
            </div>

            <div class="filter-section">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-category="all">Semua</button>
                    <button class="filter-btn" data-category="Restoran">Restoran</button>
                    <button class="filter-btn" data-category="Taman">Taman</button>
                    <button class="filter-btn" data-category="Mall">Mall</button>
                    <button class="filter-btn" data-category="Wisata">Wisata</button>
                    <button class="filter-btn" data-category="Kantor">Kantor</button>
                    <button class="filter-btn" data-category="Sekolah">Sekolah</button>
                </div>
            </div>

            <div class="locations-list">
                 <div class="list-header">
                    <span>Hasil: <span id="location-count">{{ $locations->count() }}</span> Lokasi</span>
                </div>

                @forelse($locations as $location)
                <div class="location-card"
                     data-id="{{ $location->id }}"
                     data-lat="{{ $location->latitude }}"
                     data-lng="{{ $location->longitude }}"
                     data-category="{{ $location->category }}"
                     data-name="{{ strtolower($location->name) }}"
                     data-address="{{ strtolower($location->address) }}"
                     data-description="{{ strtolower($location->description ?? '') }}">

                    @if($location->image)
                    <img src="{{ Storage::url($location->image) }}" alt="{{ $location->name }}" class="location-image">
                    @endif

                    <div class="location-name">{{ $location->name }}</div>
                    <span class="location-category">{{ $location->category }}</span>
                    <div class="location-address">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px; height:14px;"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.1.4-.27.61-.473A10.5 10.5 0 0014 15.553V9.45a8 8 0 10-8 0v6.103A10.5 10.5 0 009.09 18.28l.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
                        <span>{{ $location->address }}</span>
                    </div>
                </div>
                @empty
                <p style="text-align: center; color: #999; padding: 40px;">Belum ada lokasi yang ditambahkan.</p>
                @endforelse
                <div id="no-results">
                    <h3>Oops! Lokasi tidak ditemukan.</h3>
                    <p>Coba kata kunci atau filter yang lain.</p>
                </div>
            </div>
        </aside>

        <main class="map-container">
            <div id="map"></div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const locations = @json($locations);
            const initialView = locations.length > 0 ? [locations[0].latitude, locations[0].longitude] : [-6.5944, 106.7892];
            const map = L.map('map', { zoomControl: false }).setView(initialView, 13);
            L.control.zoom({ position: 'topright' }).addTo(map);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '<span class="cyber-text">Made by ahdiikhf_</span> | &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd', maxZoom: 20
            }).addTo(map);

            const categoryColors = {
                'Restoran': '#FF5733', 'Taman': '#28A745', 'Mall': '#FFC107',
                'Wisata': '#17A2B8', 'Kantor': '#6C757D', 'Sekolah': '#007BFF',
                'default': '#00f6ff' // Warna default neon
            };

            let markers = L.layerGroup().addTo(map);

            // === PENAMBAHAN FUNGSI EFEK DENYUT ===
            function addPulsatingHalo(locationsData) {
                if (!locationsData || locationsData.length === 0) return;

                // Hitung titik pusat dari semua lokasi
                let totalLat = 0;
                let totalLng = 0;
                locationsData.forEach(loc => {
                    totalLat += parseFloat(loc.latitude);
                    totalLng += parseFloat(loc.longitude);
                });
                const centerLat = totalLat / locationsData.length;
                const centerLng = totalLng / locationsData.length;

                // Buat custom icon untuk efek denyut
                const pulseIcon = L.divIcon({
                    className: 'pulsating-halo-container',
                    html: `<div class="pulsating-halo"></div>`,
                    iconSize: [200, 200],
                    iconAnchor: [100, 100]
                });

                // Tambahkan marker dengan efek denyut ke peta
                L.marker([centerLat, centerLng], { icon: pulseIcon, interactive: false }).addTo(map);
            }

            function createIcon(category) {
                const color = categoryColors[category] || categoryColors['default'];
                const html = `<div style="width:16px;height:16px;border-radius:50%;background-color:${color};border:2px solid white;box-shadow:0 0 10px ${color}, 0 0 15px ${color};"></div>`;
                return L.divIcon({ html: html, className: 'custom-marker-pin', iconSize: [20, 20], iconAnchor: [10, 10] });
            }

            function populateMarkers(filteredLocations) {
                markers.clearLayers();
                filteredLocations.forEach(location => {
                    let popupContent = '';
                    if (location.image) popupContent += `<img src="{{ Storage::url('') }}${location.image}" class="popup-image">`;
                    popupContent += `<div class="popup-title">${location.name}</div><span class="popup-category">${location.category}</span><p style="margin:5px 0;font-size:13px;">${location.address}</p>`;

                    const marker = L.marker([location.latitude, location.longitude], { icon: createIcon(location.category) })
                        .bindPopup(popupContent);

                    marker.locationId = location.id;

                    marker.on('click', () => {
                        setActiveCard(location.id);
                    });

                    markers.addLayer(marker);
                });
            }

            // === Fungsionalitas UI & Event Listeners (TIDAK ADA YANG DIUBAH) ===
            const filterButtons = document.querySelectorAll('.filter-btn');
            const locationCards = document.querySelectorAll('.location-card');
            const searchInput = document.getElementById('search-input');
            const locationCountEl = document.getElementById('location-count');
            const noResultsEl = document.getElementById('no-results');

            function applyFiltersAndSearch() {
                const activeCategory = document.querySelector('.filter-btn.active').dataset.category;
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;
                let filteredLocationsForMap = [];

                locationCards.forEach(card => {
                    const cardData = card.dataset;
                    const categoryMatch = activeCategory === 'all' || cardData.category === activeCategory;
                    const searchMatch = cardData.name.includes(searchTerm) || cardData.address.includes(searchTerm) || cardData.description.includes(searchTerm);

                    if (categoryMatch && searchMatch) {
                        card.classList.remove('hidden');
                        visibleCount++;
                        const originalLocation = locations.find(loc => loc.id == cardData.id);
                        if(originalLocation) filteredLocationsForMap.push(originalLocation);
                    } else {
                        card.classList.add('hidden');
                    }
                });

                populateMarkers(filteredLocationsForMap);
                locationCountEl.textContent = visibleCount;
                noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
                setActiveCard(null);
            }

            function setActiveCard(locationId) {
                locationCards.forEach(card => {
                    if (locationId && card.dataset.id == locationId) {
                        card.classList.add('active');
                        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        card.classList.remove('active');
                    }
                });
            }

            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    applyFiltersAndSearch();
                });
            });

            searchInput.addEventListener('input', applyFiltersAndSearch);

            locationCards.forEach(card => {
                card.addEventListener('click', function() {
                    const lat = parseFloat(this.dataset.lat);
                    const lng = parseFloat(this.dataset.lng);
                    const id = this.dataset.id;

                    map.flyTo([lat, lng], 16, { animate: true, duration: 1 });

                    markers.eachLayer(marker => {
                        if (marker.locationId == id) {
                            setTimeout(() => marker.openPopup(), 600);
                        }
                    });

                    setActiveCard(id);
                });
            });

            map.on('popupclose', () => setActiveCard(null));

            // Sidebar Toggle for Mobile
            const mainWrapper = document.querySelector('.main-wrapper');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const mapOverlay = document.querySelector('.map-overlay');

            sidebarToggle.addEventListener('click', () => mainWrapper.classList.toggle('sidebar-open'));
            mapOverlay.addEventListener('click', () => mainWrapper.classList.remove('sidebar-open'));

            // Initial Load
            applyFiltersAndSearch();
            addPulsatingHalo(locations); // Panggil fungsi efek denyut saat pertama kali dimuat
        });
    </script>
</body>
</html>