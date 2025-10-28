{{-- resources/views/maps/index.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Lokasi Interaktif</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --text-dark: #2d3748;
            --text-light: #64748b;
            --bg-light: #f7fafc;
            --white: #ffffff;
            --border-color: #e2e8f0;
            --shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.07), 0 2px 4px -2px rgb(0 0 0 / 0.07);
            --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.05);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 10px 10px -5px rgb(0 0 0 / 0.04);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            overflow: hidden;
        }

        .main-wrapper { display: flex; height: 100vh; position: relative; }

        /* === Sidebar === */
        .sidebar {
            width: 420px;
            background: var(--white);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1002;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-header { padding: 24px; border-bottom: 1px solid var(--border-color); }
        .sidebar-header h1 {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .search-wrapper { position: relative; margin-top: 16px; }
        .search-wrapper svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: var(--text-light); }
        #search-input { width: 100%; padding: 12px 16px 12px 44px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 14px; transition: all 0.2s; background-color: var(--bg-light); }
        #search-input:focus { outline: none; border-color: var(--secondary-color); box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2); background-color: var(--white); }

        .filter-section { padding: 16px 24px; border-bottom: 1px solid var(--border-color); }
        .filter-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
        .filter-btn { padding: 8px 16px; border: 1px solid var(--border-color); background: var(--white); color: var(--text-light); border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 12px; transition: all 0.2s; }
        .filter-btn:hover { border-color: var(--secondary-color); color: var(--secondary-color); }
        .filter-btn.active { background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); color: var(--white); border-color: transparent; }

        .locations-list { flex-grow: 1; overflow-y: auto; padding: 16px 8px 24px 24px; }
        .list-header { font-size: 14px; font-weight: 600; color: var(--text-light); padding: 0 16px 16px 0; }

        .location-card {
            background: var(--white);
            border: 1px solid transparent;
            border-left: 4px solid transparent;
            border-radius: 12px;
            padding: 16px;
            margin: 0 16px 16px 0;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: var(--shadow-sm);
        }
        .location-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .location-card.active { border-left: 4px solid var(--secondary-color); background: #f0f5ff; }
        .location-card.hidden { display: none; }
        .location-image { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; margin-bottom: 16px; transition: transform 0.3s ease; }
        .location-card:hover .location-image { transform: scale(1.03); }
        .location-name { font-size: 18px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
        .location-category { display: inline-block; padding: 5px 12px; background-color: var(--bg-light); color: var(--primary-color); border-radius: 20px; font-size: 11px; font-weight: 700; margin-bottom: 12px; }
        .location-address { color: var(--text-light); font-size: 13px; display: flex; align-items: start; gap: 8px; line-height: 1.5; }
        .location-address svg { margin-top: 3px; min-width: 14px; }
        
        #no-results { text-align: center; color: #999; padding: 50px 20px; display: none; }

        /* === Map Area === */
        .map-container { flex-grow: 1; position: relative; background: #eee; }
        #map { height: 100%; width: 100%; z-index: 1; }
        .leaflet-top.leaflet-left { top: 78px; }

        /* === Mobile Responsive & Sidebar Toggle === */
        #sidebar-toggle { display: none; position: absolute; top: 15px; left: 15px; z-index: 1003; width: 45px; height: 45px; background: var(--white); border: 1px solid var(--border-color); border-radius: 50%; cursor: pointer; box-shadow: var(--shadow-md); justify-content: center; align-items: center; }
        .map-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; }

        @media (max-width: 768px) {
            #sidebar-toggle { display: flex; }
            .sidebar { position: absolute; height: 100%; top: 0; left: 0; transform: translateX(-100%); box-shadow: var(--shadow-lg); }
            .main-wrapper.sidebar-open .sidebar { transform: translateX(0); }
            .main-wrapper.sidebar-open .map-overlay { display: block; }
        }

        /* === Custom Marker & Popup Style === */
        @keyframes marker-pop { 0% { transform: scale(0.5) rotate(-45deg); opacity: 0; } 100% { transform: scale(1) rotate(-45deg); opacity: 1; } }
        .custom-marker-pin { animation: marker-pop 0.3s ease-out forwards; }
        .leaflet-popup-content-wrapper { border-radius: 10px !important; box-shadow: var(--shadow-md); min-width: 280px; /* Lebar popup diubah di sini */ }
        .leaflet-popup-content { margin: 15px !important; font-family: 'Plus Jakarta Sans', sans-serif; }
        .popup-image { width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
        .popup-title { font-size: 16px; font-weight: 700; margin: 0 0 5px 0; }
        .popup-category { display: inline-block; padding: 4px 10px; background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 10px; font-size: 10px; font-weight: 600; margin-bottom: 8px; }

        /* === Scrollbar === */
        .locations-list::-webkit-scrollbar { width: 8px; }
        .locations-list::-webkit-scrollbar-track { background: transparent; }
        .locations-list::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; border: 2px solid var(--white); }
        .locations-list::-webkit-scrollbar-thumb:hover { background: #aaa; }

        /* JIKA INGIN MENGHAPUS WATERMARK (TIDAK DISARANKAN) */
        /* .leaflet-control-attribution { display: none !important; } */
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
                <h1>🗺️ Jelajah Peta</h1>
                <div class="search-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" id="search-input" placeholder="Cari nama lokasi, alamat...">
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

            // GANTI TILE LAYER KE OPENSTREETMAP STANDAR (LEBIH DETAIL)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);
            
            const categoryColors = {
                'Restoran': '#FF5733', 'Taman': '#28A745', 'Mall': '#FFC107',
                'Wisata': '#17A2B8', 'Kantor': '#6C757D', 'Sekolah': '#007BFF'
            };

            let markers = L.layerGroup().addTo(map);

            // GANTI FUNGSI IKON KE MODEL PIN
            function createIcon(category) {
                const color = categoryColors[category] || '#6a11cb';
                const html = `
                    <div style="
                        background-color: ${color}; 
                        width: 28px; height: 28px; 
                        border-radius: 50% 50% 50% 0; 
                        transform: rotate(-45deg);
                        border: 3px solid white; 
                        box-shadow: 0 2px 5px rgba(0,0,0,0.4);
                        display: flex; justify-content: center; align-items: center;
                    ">
                        <div style="
                            width: 12px; height: 12px;
                            background-color: white;
                            border-radius: 50%;
                        "></div>
                    </div>`;
                return L.divIcon({
                    html: html,
                    className: 'custom-marker-pin',
                    iconSize: [30, 30],
                    iconAnchor: [0, 15]
                });
            }
            
            function populateMarkers(filteredLocations) {
                markers.clearLayers();
                filteredLocations.forEach(location => {
                    let popupContent = '';
                    if (location.image) popupContent += `<img src="/storage/${location.image}" class="popup-image">`;
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

            // === Fungsionalitas UI & Event Listeners ===
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
        });
    </script>
</body>
</html>