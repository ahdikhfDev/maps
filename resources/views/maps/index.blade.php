{{-- resources/views/maps/index.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Lokasi Interaktif</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
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
            --shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            overflow: hidden;
        }

        .main-wrapper {
            display: flex;
            height: 100vh;
        }

        /* === Sidebar (Daftar Lokasi) === */
        .sidebar {
            width: 420px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            z-index: 1001;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-header h1 {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .search-wrapper {
            position: relative;
            margin-top: 16px;
        }

        .search-wrapper svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: var(--text-light);
        }

        #search-input {
            width: 100%;
            padding: 12px 16px 12px 40px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 14px;
            transition: all 0.2s;
            background-color: var(--white);
        }

        #search-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
        }

        .filter-section {
            padding: 16px 24px;
        }

        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            background: var(--white);
            color: var(--text-light);
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.2s;
        }

        .filter-btn:hover {
            background-color: var(--bg-light);
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }

        .filter-btn.active {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border-color: transparent;
        }

        .locations-list {
            flex-grow: 1;
            overflow-y: auto;
            padding: 0 24px 24px 24px;
        }

        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-light);
        }

        .location-card {
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .location-card:hover {
            border-color: var(--secondary-color);
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        .location-card.hidden {
            display: none;
        }

        .location-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .location-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .location-category {
            display: inline-block;
            padding: 4px 12px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .location-address {
            color: var(--text-light);
            font-size: 13px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .location-description {
            color: #4a5568;
            font-size: 13px;
            line-height: 1.5;
        }

        #no-results {
            text-align: center;
            color: #999;
            padding: 50px 20px;
            display: none;
        }

        /* === Area Peta === */
        .map-container {
            flex-grow: 1;
            position: relative;
        }

        #map {
            height: 100%;
            width: 100%;
            z-index: 1;
        }

        /* Style Popup Kustom */
        .leaflet-popup-content-wrapper {
            border-radius: 10px !important;
            box-shadow: var(--shadow-md);
        }
        .leaflet-popup-content {
            margin: 15px !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .popup-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .popup-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }
        .popup-category {
            display: inline-block;
            padding: 3px 10px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        /* === Scrollbar === */
        .locations-list::-webkit-scrollbar { width: 6px; }
        .locations-list::-webkit-scrollbar-track { background: transparent; }
        .locations-list::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        .locations-list::-webkit-scrollbar-thumb:hover { background: #aaa; }

        /* === Responsif === */
        @media (max-width: 900px) {
            .main-wrapper {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: 50vh;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
            .map-container {
                height: 50vh;
            }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>🗺️ Peta Lokasi</h1>
                <!-- Search Bar Ditambahkan Di Sini -->
                <div class="search-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
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
                    <span>Hasil: <span id="location-count">{{ $locations->count() }}</span></span>
                </div>

                @forelse($locations as $location)
                <div class="location-card"
                     data-category="{{ $location->category }}"
                     data-lat="{{ $location->latitude }}"
                     data-lng="{{ $location->longitude }}"
                     data-name="{{ strtolower($location->name) }}"
                     data-address="{{ strtolower($location->address) }}"
                     data-description="{{ strtolower($location->description ?? '') }}"> <!-- Menangani null description -->

                    @if($location->image)
                    <img src="{{ Storage::url($location->image) }}"
                         alt="{{ $location->name }}"
                         class="location-image">
                    @endif

                    <div class="location-name">{{ $location->name }}</div>
                    <span class="location-category">{{ $location->category }}</span>
                    <div class="location-address">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px; min-width:16px;">
                            <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.1.4-.27.61-.473A10.5 10.5 0 0014 15.553V9.45a8 8 0 10-8 0v6.103A10.5 10.5 0 009.09 18.28l.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                        </svg>
                        {{ $location->address }}
                    </div>

                    @if($location->description)
                    <p class="location-description">{{ Str::limit($location->description, 80) }}</p>
                    @endif
                </div>
                @empty
                <p style="text-align: center; color: #999; padding: 40px;">
                    Belum ada lokasi yang ditambahkan.
                </p>
                @endforelse
                <div id="no-results">
                    <h3>Oops!</h3>
                    <p>Lokasi yang Anda cari tidak ditemukan.</p>
                </div>
            </div>
        </aside>

        <main class="map-container">
            <div id="map"></div>
        </main>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data lokasi dari Laravel
            const locations = @json($locations);

            // Inisialisasi peta
            const initialView = locations.length > 0 ? [locations[0].latitude, locations[0].longitude] : [-6.5944, 106.7892];
            const map = L.map('map').setView(initialView, 13);

            // Tambahkan tile layer (menggunakan Voyager dari CARTO)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

            // Definisikan warna untuk setiap kategori
            const categoryColors = {
                'Restoran': '#FF5733', 'Taman': '#28A745', 'Mall': '#FFC107',
                'Wisata': '#17A2B8', 'Kantor': '#6C757D', 'Sekolah': '#007BFF'
            };

            let markers = [];

            // Fungsi untuk membuat ikon kustom
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
                    className: 'custom-marker',
                    iconSize: [30, 30],
                    iconAnchor: [0, 15]
                });
            }

            // Tambahkan markers ke peta
            locations.forEach(location => {
                const icon = createIcon(location.category);

                let popupContent = '';
                if (location.image) {
                    popupContent += `<img src="/storage/${location.image}" class="popup-image">`;
                }
                popupContent += `
                    <div class="popup-title">${location.name}</div>
                    <span class="popup-category">${location.category}</span>
                    <p style="margin: 5px 0; font-size: 13px;">${location.address}</p>
                `;
                if (location.description) {
                    popupContent += `<p style="font-size: 12px; color: #666;">${location.description.substring(0, 100)}...</p>`;
                }

                const marker = L.marker([location.latitude, location.longitude], { icon })
                    .bindPopup(popupContent);

                marker.locationData = location; // Simpan data asli di marker
                markers.push(marker);
            });

            // === Fungsionalitas Filter dan Pencarian ===

            const filterButtons = document.querySelectorAll('.filter-btn');
            const locationCards = document.querySelectorAll('.location-card');
            const searchInput = document.getElementById('search-input');
            const locationCountEl = document.getElementById('location-count');
            const noResultsEl = document.getElementById('no-results');

            function applyFilters() {
                const activeCategory = document.querySelector('.filter-btn.active').dataset.category;
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                locationCards.forEach(card => {
                    const cardCategory = card.dataset.category;
                    const cardName = card.dataset.name;
                    const cardAddress = card.dataset.address;
                    const cardDescription = card.dataset.description;

                    const categoryMatch = activeCategory === 'all' || cardCategory === activeCategory;
                    const searchMatch = cardName.includes(searchTerm) || cardAddress.includes(searchTerm) || cardDescription.includes(searchTerm);

                    if (categoryMatch && searchMatch) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                markers.forEach(marker => {
                    map.removeLayer(marker);
                    const markerCategory = marker.locationData.category;
                    const markerName = marker.locationData.name.toLowerCase();
                    const markerAddress = marker.locationData.address.toLowerCase();
                    const markerDescription = (marker.locationData.description || '').toLowerCase(); // Handle null

                    const categoryMatch = activeCategory === 'all' || markerCategory === activeCategory;
                    const searchMatch = markerName.includes(searchTerm) || markerAddress.includes(searchTerm) || markerDescription.includes(searchTerm);

                    if (categoryMatch && searchMatch) {
                        marker.addTo(map);
                    }
                });

                locationCountEl.textContent = visibleCount;
                noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    applyFilters();
                });
            });

            searchInput.addEventListener('input', applyFilters);

            locationCards.forEach(card => {
                card.addEventListener('click', function() {
                    const lat = parseFloat(this.dataset.lat);
                    const lng = parseFloat(this.dataset.lng);

                    map.flyTo([lat, lng], 16, {
                        animate: true,
                        duration: 1
                    });

                    const correspondingMarker = markers.find(marker => {
                        const markerLatLng = marker.getLatLng();
                        return markerLatLng.lat === lat && markerLatLng.lng === lng;
                    });

                    if (correspondingMarker) {
                        setTimeout(() => {
                            correspondingMarker.openPopup();
                        }, 800);
                    }
                });
            });

            // Tampilkan semua marker awalnya
            applyFilters();
        });
    </script>
</body>
</html>