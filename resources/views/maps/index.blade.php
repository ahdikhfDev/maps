{{-- resources/views/maps/index.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Lokasi</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            margin-bottom: 5px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .filter-btn:hover, .filter-btn.active {
            background: #667eea;
            color: white;
        }

        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }

        #map {
            height: 600px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .locations-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            max-height: 600px;
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .location-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .location-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .location-card.hidden {
            display: none;
        }

        .location-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .location-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .location-category {
            display: inline-block;
            padding: 4px 12px;
            background: #667eea;
            color: white;
            border-radius: 12px;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .location-address {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .location-description {
            color: #888;
            font-size: 13px;
        }

        .stats {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #999;
        }

        @media (max-width: 1024px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }

            .locations-list {
                max-height: 400px;
            }
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px;
        }

        .popup-image {
            width: 200px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .popup-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .popup-category {
            display: inline-block;
            padding: 3px 10px;
            background: #667eea;
            color: white;
            border-radius: 10px;
            font-size: 11px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗺️ Peta Lokasi Interaktif</h1>
        <p>Jelajahi lokasi menarik di sekitar Anda</p>
    </div>

    <div class="container">
        <div class="filter-section">
            <h3 style="margin-bottom: 15px;">Filter Kategori:</h3>
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

        <div class="content-wrapper">
            <div id="map"></div>

            <div class="locations-list">
                <h3 style="margin-bottom: 15px;">Daftar Lokasi ({{ $locations->count() }})</h3>
                
                @forelse($locations as $location)
                <div class="location-card" 
                     data-category="{{ $location->category }}"
                     data-lat="{{ $location->latitude }}" 
                     data-lng="{{ $location->longitude }}">
                    
                    @if($location->image)
                    <img src="{{ Storage::url($location->image) }}" 
                         alt="{{ $location->name }}" 
                         class="location-image">
                    @endif

                    <div class="location-name">{{ $location->name }}</div>
                    <span class="location-category">{{ $location->category }}</span>
                    <div class="location-address">📍 {{ $location->address }}</div>
                    
                    @if($location->description)
                    <div class="location-description">{{ Str::limit($location->description, 100) }}</div>
                    @endif

                    <div class="stats">
                        <span>📌 {{ $location->latitude }}, {{ $location->longitude }}</span>
                    </div>
                </div>
                @empty
                <p style="text-align: center; color: #999; padding: 40px;">
                    Belum ada lokasi yang ditambahkan
                </p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Data lokasi dari Laravel
        const locations = @json($locations);

        // Inisialisasi peta (centered di Bogor)
        const map = L.map('map').setView([-6.5944, 106.7892], 13);

        // Tambahkan tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Icon warna berdasarkan kategori
        const categoryColors = {
            'Restoran': '#FF5733',
            'Taman': '#28A745',
            'Mall': '#FFC107',
            'Wisata': '#17A2B8',
            'Kantor': '#6C757D',
            'Sekolah': '#007BFF'
        };

        let markers = [];

        // Fungsi untuk membuat custom icon
        function createIcon(category) {
            const color = categoryColors[category] || '#667eea';
            return L.divIcon({
                html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                className: 'custom-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });
        }

        // Tambahkan markers
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
                popupContent += `<p style="font-size: 12px; color: #666;">${location.description}</p>`;
            }

            const marker = L.marker([location.latitude, location.longitude], { icon })
                .addTo(map)
                .bindPopup(popupContent);

            marker.category = location.category;
            markers.push(marker);
        });

        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        const locationCards = document.querySelectorAll('.location-card');

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const category = this.dataset.category;
                
                // Update active button
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Filter markers
                markers.forEach(marker => {
                    if (category === 'all' || marker.category === category) {
                        marker.addTo(map);
                    } else {
                        map.removeLayer(marker);
                    }
                });

                // Filter location cards
                locationCards.forEach(card => {
                    if (category === 'all' || card.dataset.category === category) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });
        });

        // Click location card to zoom map
        locationCards.forEach(card => {
            card.addEventListener('click', function() {
                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                map.setView([lat, lng], 16);
                
                // Find and open corresponding marker popup
                markers.forEach(marker => {
                    const markerLatLng = marker.getLatLng();
                    if (markerLatLng.lat === lat && markerLatLng.lng === lng) {
                        marker.openPopup();
                    }
                });
            });
        });
    </script>
</body>
</html>