@extends('layouts.app')

@section('title', '发布走失信息')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-3xl">🐾</span>
            <div>
                <h1 class="text-xl font-semibold text-neutral-800">发布走失宠物信息</h1>
                <p class="text-sm text-neutral-500">请填写详细信息，帮助大家一起寻找</p>
            </div>
        </div>

        <form action="{{ route('lost-pets.store') }}" method="POST" enctype="multipart/form-data" id="lost-pet-form">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">宠物类型 *</label>
                    <select name="pet_type" class="input-field" required>
                        <option value="dog" {{ old('pet_type') === 'dog' ? 'selected' : '' }}>🐕 狗狗</option>
                        <option value="cat" {{ old('pet_type') === 'cat' ? 'selected' : '' }}>🐱 猫咪</option>
                        <option value="other" {{ old('pet_type') === 'other' ? 'selected' : '' }}>🐾 其他</option>
                    </select>
                    @error('pet_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">宠物名字</label>
                    <input type="text" name="pet_name" value="{{ old('pet_name') }}" 
                           placeholder="如：豆豆、咪咪" 
                           class="input-field">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">品种</label>
                    <input type="text" name="breed" value="{{ old('breed') }}" 
                           placeholder="如：金毛、英短、土狗" 
                           class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">颜色</label>
                    <input type="text" name="color" value="{{ old('color') }}" 
                           placeholder="如：黄色、黑白、灰色" 
                           class="input-field">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700 mb-1">🔔 项圈特征</label>
                <input type="text" name="collar_features" value="{{ old('collar_features') }}" 
                       placeholder="如：红色项圈带铃铛、蓝色背带、有狗牌写着名字" 
                       class="input-field">
                <p class="text-xs text-neutral-500 mt-1">项圈是识别宠物的重要特征，请尽量详细描述</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700 mb-1">照片</label>
                <div class="flex items-start gap-4">
                    <div id="photo-preview" class="w-32 h-32 bg-neutral-100 rounded-lg flex items-center justify-center text-4xl text-neutral-300 border-2 border-dashed border-neutral-200">
                        📷
                    </div>
                    <div class="flex-1">
                        <input type="file" name="photo" id="photo-input" accept="image/*" class="input-field text-sm">
                        <p class="text-xs text-neutral-500 mt-1">支持 JPG、PNG、GIF 格式，最大 5MB</p>
                        @error('photo')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700 mb-1">📝 详细描述</label>
                <textarea name="description" rows="4" placeholder="描述宠物的特征、习性、走失时的情况等..." 
                          class="input-field">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700 mb-2">📍 最后出现地点 *</label>
                <p class="text-xs text-neutral-500 mb-2">在地图上点击标注宠物最后出现的位置</p>
                <div id="lost-pet-map" class="w-full h-[300px] rounded-lg overflow-hidden border border-neutral-200 mb-2"></div>
                <input type="hidden" name="last_seen_lat" id="last-seen-lat" value="{{ old('last_seen_lat') }}">
                <input type="hidden" name="last_seen_lng" id="last-seen-lng" value="{{ old('last_seen_lng') }}">
                <input type="text" name="last_seen_address" id="last-seen-address" value="{{ old('last_seen_address') }}" 
                       placeholder="或输入详细地址" 
                       class="input-field">
                @error('last_seen_lat')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('last_seen_lng')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('last_seen_address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">⏰ 最后出现时间 *</label>
                    <input type="datetime-local" name="last_seen_at" value="{{ old('last_seen_at', date('Y-m-d\TH:i')) }}" 
                           class="input-field" required>
                    @error('last_seen_at')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">📞 联系电话 *</label>
                    <input type="tel" name="contact_phone" value="{{ old('contact_phone') }}" 
                           placeholder="请输入您的联系电话" 
                           class="input-field" required>
                    @error('contact_phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-1">联系人姓名</label>
                <input type="text" name="contact_name" value="{{ old('contact_name', auth()->user()?->username) }}" 
                       placeholder="您的称呼" 
                       class="input-field">
            </div>

            <div class="flex gap-3">
                <a href="{{ route('lost-pets.index') }}" class="btn-secondary flex-1">取消</a>
                <button type="submit" class="btn-primary flex-1">发布信息</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .custom-marker {
        background: transparent;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .marker-pin {
        width: 36px;
        height: 36px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ef4444;
    }
    .marker-pin span {
        transform: rotate(45deg);
        font-size: 18px;
    }
    .marker-pulse {
        position: absolute;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        animation: pulse 2s infinite;
        opacity: 0.7;
        background: #ef4444;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.7; }
        50% { transform: scale(2.5); opacity: 0; }
        100% { transform: scale(1); opacity: 0; }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('lost-pet-map').setView([39.9042, 116.4074], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                map.setView([pos.coords.latitude, pos.coords.longitude], 15);
            },
            function() {
                console.log('无法获取当前位置');
            }
        );
    }

    let selectedMarker = null;

    function createMarker(latlng) {
        return L.divIcon({
            className: 'custom-marker',
            html: `
                <div class="marker-pulse"></div>
                <div class="marker-pin">
                    <span>📍</span>
                </div>
            `,
            iconSize: [36, 36],
            iconAnchor: [18, 36]
        });
    }

    map.on('click', function(e) {
        if (selectedMarker) {
            map.removeLayer(selectedMarker);
        }
        selectedMarker = L.marker(e.latlng, { icon: createMarker(e.latlng) }).addTo(map);

        document.getElementById('last-seen-lat').value = e.latlng.lat.toFixed(7);
        document.getElementById('last-seen-lng').value = e.latlng.lng.toFixed(7);

        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}&zoom=18&addressdetails=1`)
            .then(res => res.json())
            .then(data => {
                if (data.display_name) {
                    document.getElementById('last-seen-address').value = data.display_name;
                }
            })
            .catch(() => {});
    });

    const latValue = document.getElementById('last-seen-lat').value;
    const lngValue = document.getElementById('last-seen-lng').value;
    if (latValue && lngValue) {
        selectedMarker = L.marker([parseFloat(latValue), parseFloat(lngValue)], {
            icon: createMarker([parseFloat(latValue), parseFloat(lngValue)])
        }).addTo(map);
        map.setView([parseFloat(latValue), parseFloat(lngValue)], 15);
    }

    const photoInput = document.getElementById('photo-input');
    const photoPreview = document.getElementById('photo-preview');

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.innerHTML = `<img src="${e.target.result}" alt="预览" class="w-full h-full object-cover rounded-lg">`;
            };
            reader.readAsDataURL(file);
        }
    });

    const form = document.getElementById('lost-pet-form');
    form.addEventListener('submit', function(e) {
        const lat = document.getElementById('last-seen-lat').value;
        const lng = document.getElementById('last-seen-lng').value;
        if (!lat || !lng) {
            e.preventDefault();
            alert('请在地图上点击标注宠物最后出现的位置');
            return false;
        }
    });
});
</script>
@endpush
