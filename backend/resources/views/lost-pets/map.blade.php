@extends('layouts.app')

@section('title', '宠物寻回地图')

@section('content')
<div class="mb-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-neutral-800">🗺️ 宠物寻回地图</h1>
            <p class="text-sm text-neutral-500 mt-1">在地图上查看走失宠物和线索位置</p>
        </div>
        @auth
            <a href="{{ route('lost-pets.create') }}" class="btn-primary">
                <span class="mr-1">➕</span> 发布走失信息
            </a>
        @else
            <a href="{{ route('login') }}" class="btn-primary">
                <span class="mr-1">🔐</span> 登录后发布
            </a>
        @endauth
    </div>
</div>

<div class="mb-4 flex flex-wrap gap-2 items-center">
    <form method="GET" action="{{ route('lost-pets.map') }}" class="flex flex-wrap gap-2 items-center">
        <select name="status" class="input-field w-auto text-sm" onchange="this.form.submit()">
            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>全部状态</option>
            <option value="lost" {{ $status == 'lost' ? 'selected' : '' }}>走失中</option>
            <option value="found" {{ $status == 'found' ? 'selected' : '' }}>已找到</option>
        </select>
        <select name="pet_type" class="input-field w-auto text-sm" onchange="this.form.submit()">
            <option value="all" {{ $petType == 'all' ? 'selected' : '' }}>全部类型</option>
            <option value="dog" {{ $petType == 'dog' ? 'selected' : '' }}>🐕 狗狗</option>
            <option value="cat" {{ $petType == 'cat' ? 'selected' : '' }}>🐱 猫咪</option>
            <option value="other" {{ $petType == 'other' ? 'selected' : '' }}>🐾 其他</option>
        </select>
    </form>
    <a href="{{ route('lost-pets.index') }}" class="btn-secondary text-sm px-3">
        📋 列表视图
    </a>
    <div class="flex items-center gap-4 ml-auto text-xs text-neutral-500">
        <span class="flex items-center gap-1">
            <span class="w-3 h-3 rounded-full bg-red-500"></span> 走失狗狗
        </span>
        <span class="flex items-center gap-1">
            <span class="w-3 h-3 rounded-full bg-amber-500"></span> 走失猫咪
        </span>
        <span class="flex items-center gap-1">
            <span class="w-3 h-3 rounded-full bg-green-500"></span> 已找到
        </span>
        <span class="flex items-center gap-1">
            <span class="w-3 h-3 rounded-full bg-blue-500"></span> 线索标记
        </span>
    </div>
</div>

<div class="card p-0 overflow-hidden">
    <div id="pet-map" class="w-full h-[600px]"></div>
</div>

<div id="map-popup-template" class="hidden">
    <div class="map-popup p-2 min-w-[200px]">
        <div class="font-semibold text-sm mb-1" data-popup-title></div>
        <div class="text-xs text-neutral-600 mb-2" data-popup-address></div>
        <div class="text-xs text-neutral-500 mb-2" data-popup-time></div>
        <a href="" class="text-xs text-primary-600 hover:underline" data-popup-link>查看详情 →</a>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .map-popup .leaflet-popup-content-wrapper {
        border-radius: 8px;
        padding: 0;
    }
    .map-popup .leaflet-popup-content {
        margin: 0;
        min-width: 220px;
    }
    .custom-marker {
        background: transparent;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .marker-pin {
        width: 32px;
        height: 32px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .marker-pin span {
        transform: rotate(45deg);
        font-size: 16px;
    }
    .marker-pulse {
        position: absolute;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        animation: pulse 2s infinite;
        opacity: 0.7;
    }
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0.7;
        }
        50% {
            transform: scale(2);
            opacity: 0;
        }
        100% {
            transform: scale(1);
            opacity: 0;
        }
    }
    .clue-marker .marker-pin {
        width: 24px;
        height: 24px;
    }
    .clue-marker .marker-pin span {
        font-size: 12px;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('pet-map').setView([39.9042, 116.4074], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const markers = L.layerGroup().addTo(map);
    const clueMarkers = L.layerGroup().addTo(map);

    function createCustomMarker(color, emoji, isClue = false) {
        const markerClass = isClue ? 'clue-marker' : '';
        return L.divIcon({
            className: `custom-marker ${markerClass}`,
            html: `
                <div class="marker-pulse" style="background: ${color}; opacity: 0.3;"></div>
                <div class="marker-pin" style="background: ${color};">
                    <span>${emoji}</span>
                </div>
            `,
            iconSize: isClue ? [24, 24] : [32, 32],
            iconAnchor: isClue ? [12, 24] : [16, 32],
            popupAnchor: [0, -32]
        });
    }

    function loadMarkers() {
        const params = new URLSearchParams(window.location.search);
        const url = `/api/lost-pets/map-markers?${params.toString()}`;

        fetch(url)
            .then(response => response.json())
            .then(result => {
                markers.clearLayers();
                clueMarkers.clearLayers();

                const pets = result.data;
                const bounds = [];

                pets.forEach(pet => {
                    const color = getMarkerColor(pet.status, pet.pet_type);
                    const emoji = getPetEmoji(pet.pet_type);
                    const marker = L.marker([pet.last_seen_lat, pet.last_seen_lng], {
                        icon: createCustomMarker(color, emoji)
                    });

                    const popupContent = `
                        <div class="p-3 min-w-[220px]">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-2xl">${emoji}</span>
                                <div>
                                    <div class="font-semibold text-sm">
                                        ${pet.pet_name || '未命名宠物'}
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium ${getStatusBadgeClass(pet.status)}">
                                        ${getStatusName(pet.status)}
                                    </span>
                                </div>
                            </div>
                            <div class="text-xs text-neutral-600 mb-1">
                                📍 ${pet.last_seen_address}
                            </div>
                            <div class="text-xs text-neutral-500 mb-2">
                                ⏰ ${formatDate(pet.last_seen_at)}
                            </div>
                            <a href="/lost-pets/${pet.id}" class="text-xs text-primary-600 hover:underline font-medium">
                                查看详情 →
                            </a>
                        </div>
                    `;

                    marker.bindPopup(popupContent, { className: 'map-popup' });
                    markers.addLayer(marker);
                    bounds.push([pet.last_seen_lat, pet.last_seen_lng]);

                    fetch(`/api/lost-pets/${pet.id}/clues`)
                        .then(res => res.json())
                        .then(clueResult => {
                            clueResult.data.forEach(clue => {
                                if (clue.lat && clue.lng) {
                                    const clueMarker = L.marker([clue.lat, clue.lng], {
                                        icon: createCustomMarker('#3b82f6', '📍', true)
                                    });

                                    const cluePopup = `
                                        <div class="p-3 min-w-[200px]">
                                            <div class="font-semibold text-sm mb-1">💡 线索</div>
                                            <div class="text-xs text-neutral-600 mb-1">
                                                ${clue.address || '已标记位置'}
                                            </div>
                                            <div class="text-xs text-neutral-500 mb-2">
                                                ⏰ ${formatDate(clue.seen_at)}
                                            </div>
                                            <div class="text-xs text-neutral-600 mb-2">
                                                ${clue.description ? clue.description.substring(0, 50) + '...' : ''}
                                            </div>
                                            <a href="/lost-pets/${pet.id}" class="text-xs text-primary-600 hover:underline">
                                                查看详情 →
                                            </a>
                                        </div>
                                    `;

                                    clueMarker.bindPopup(cluePopup, { className: 'map-popup' });
                                    clueMarkers.addLayer(clueMarker);
                                }
                            });
                        });
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [50, 50] });
                }
            });
    }

    function getMarkerColor(status, petType) {
        if (status === 'found') return '#10b981';
        if (status === 'closed') return '#6b7280';
        return petType === 'dog' ? '#ef4444' : (petType === 'cat' ? '#f59e0b' : '#8b5cf6');
    }

    function getPetEmoji(petType) {
        return petType === 'dog' ? '🐕' : (petType === 'cat' ? '🐱' : '🐾');
    }

    function getStatusName(status) {
        const names = { lost: '走失中', found: '已找到', closed: '已关闭' };
        return names[status] || status;
    }

    function getStatusBadgeClass(status) {
        const classes = {
            lost: 'bg-red-50 text-red-700',
            found: 'bg-green-50 text-green-700',
            closed: 'bg-gray-50 text-gray-700'
        };
        return classes[status] || 'bg-neutral-100 text-neutral-600';
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleString('zh-CN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    loadMarkers();
});
</script>
@endpush
