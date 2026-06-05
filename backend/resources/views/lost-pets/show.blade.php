@extends('layouts.app')

@section('title', $lostPet->pet_name ? $lostPet->pet_name . ' - ' : '' . '宠物寻回详情')

@section('content')
<div class="flex flex-col lg:flex-row gap-6">
    <div class="flex-1 space-y-6">
        <div class="card">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                <div class="flex items-start gap-3">
                    <span class="text-4xl">
                        {{ $lostPet->pet_type === 'dog' ? '🐕' : ($lostPet->pet_type === 'cat' ? '🐱' : '🐾') }}
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-semibold text-neutral-800">
                                {{ $lostPet->pet_name ? $lostPet->pet_name : '未命名宠物' }}
                            </h1>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ pet_status_badge_class($lostPet->status) }}">
                                {{ pet_status_name($lostPet->status) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-neutral-500">
                            <span>{{ pet_type_name($lostPet->pet_type) }}</span>
                            @if($lostPet->breed)
                                <span>· {{ $lostPet->breed }}</span>
                            @endif
                            @if($lostPet->color)
                                <span>· {{ $lostPet->color }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($isOwner && $lostPet->status === 'lost')
                        <button onclick="showMarkFoundModal()" class="btn-secondary text-sm">
                            ✅ 已找到
                        </button>
                    @endif
                    @if($isOwner || auth()->user()?->isAdmin())
                        <a href="{{ route('lost-pets.edit', $lostPet) }}" class="btn-secondary text-sm">
                            ✏️ 编辑
                        </a>
                        @if(auth()->user()?->isAdmin() && $lostPet->status !== 'closed')
                            <form action="{{ route('lost-pets.close', $lostPet) }}" method="POST" class="inline" onsubmit="return confirm('确定要关闭这个帖子吗？')">
                                @csrf
                                <button type="submit" class="btn-secondary text-sm text-red-600 hover:text-red-700">
                                    🚫 关闭
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('lost-pets.destroy', $lostPet) }}" method="POST" class="inline" onsubmit="return confirm('确定要删除这个帖子吗？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-secondary text-sm text-red-600 hover:text-red-700">
                                🗑️ 删除
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if($lostPet->status === 'found' && $lostPet->thank_you_note)
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xl">🎉</span>
                        <span class="font-semibold text-green-800">感谢启事</span>
                    </div>
                    <p class="text-green-700 text-sm">{{ $lostPet->thank_you_note }}</p>
                </div>
            @endif

            @if($lostPet->photo_path)
                <div class="mb-4 rounded-lg overflow-hidden bg-neutral-100">
                    <img src="{{ Storage::url($lostPet->photo_path) }}" alt="{{ $lostPet->pet_name ?? '宠物照片' }}" 
                         class="w-full max-h-96 object-contain">
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="p-3 bg-neutral-50 rounded-lg">
                    <div class="text-xs text-neutral-500 mb-1">📍 最后出现地点</div>
                    <div class="text-sm text-neutral-800">{{ $lostPet->last_seen_address }}</div>
                </div>
                <div class="p-3 bg-neutral-50 rounded-lg">
                    <div class="text-xs text-neutral-500 mb-1">⏰ 最后出现时间</div>
                    <div class="text-sm text-neutral-800">{{ $lostPet->last_seen_at->format('Y-m-d H:i') }}</div>
                </div>
                @if($lostPet->collar_features)
                    <div class="p-3 bg-neutral-50 rounded-lg">
                        <div class="text-xs text-neutral-500 mb-1">🔔 项圈特征</div>
                        <div class="text-sm text-neutral-800">{{ $lostPet->collar_features }}</div>
                    </div>
                @endif
                <div class="p-3 bg-neutral-50 rounded-lg">
                    <div class="text-xs text-neutral-500 mb-1">📞 联系方式</div>
                    <div class="text-sm text-neutral-800">
                        {{ $lostPet->contact_name ?? '' }} {{ $lostPet->contact_phone }}
                    </div>
                </div>
            </div>

            @if($lostPet->description)
                <div class="mb-4">
                    <div class="text-sm font-medium text-neutral-700 mb-2">详细描述</div>
                    <p class="text-sm text-neutral-600 whitespace-pre-wrap">{{ $lostPet->description }}</p>
                </div>
            @endif

            <div class="flex items-center justify-between pt-4 border-t border-neutral-100 text-sm text-neutral-500">
                <div class="flex items-center gap-2">
                    <span>发布者：{{ $lostPet->user->username }}</span>
                    <span>·</span>
                    <span>{{ $lostPet->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <span>👁️ {{ $lostPet->view_count }}</span>
                    <span>💡 {{ $lostPet->clue_count }} 条线索</span>
                </div>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-3 border-b border-neutral-100">
                <h2 class="font-semibold text-neutral-800">🗺️ 位置地图</h2>
            </div>
            <div id="detail-map" class="w-full h-[350px]"></div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-neutral-800">💡 线索列表</h2>
                <span class="text-sm text-neutral-500">共 {{ $clues->count() }} 条线索</span>
            </div>

            @if($clues->isEmpty())
                <div class="text-center py-8 text-neutral-500">
                    <div class="text-4xl mb-2">🔍</div>
                    <p>暂无线索，快来提供第一条线索吧！</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($clues as $clue)
                        <div class="p-4 bg-neutral-50 rounded-lg">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📍</span>
                                    <div>
                                        <div class="text-sm font-medium text-neutral-800">
                                            {{ $clue->address ?: '已标记位置' }}
                                            @if($clue->is_private)
                                                <span class="ml-2 text-xs text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">🔒 隐私</span>
                                            @endif
                                            @if($clue->is_verified)
                                                <span class="ml-2 text-xs text-green-600 bg-green-50 px-1.5 py-0.5 rounded">✅ 已核实</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-neutral-500">
                                            {{ $clue->user->username }} · {{ $clue->seen_at->format('Y-m-d H:i') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    @if($isOwner && !$clue->is_verified)
                                        <form action="{{ route('pet-clues.verify', $clue) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs text-green-600 hover:text-green-700 px-2 py-1">核实</button>
                                        </form>
                                    @endif
                                    @if($clue->canEdit(auth()->user()))
                                        <form action="{{ route('pet-clues.destroy', $clue) }}" method="POST" class="inline" onsubmit="return confirm('确定删除这条线索吗？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-700 px-2 py-1">删除</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @if($clue->description)
                                <p class="text-sm text-neutral-600 mb-2 whitespace-pre-wrap">{{ $clue->description }}</p>
                            @endif
                            @if($clue->photo_path && $clue->canView(auth()->user()))
                                <img src="{{ Storage::url($clue->photo_path) }}" alt="线索照片" 
                                     class="max-w-xs rounded-lg mt-2">
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="lg:w-80 space-y-6">
        @if(auth()->check() && $lostPet->status === 'lost')
            <div class="card">
                <h3 class="font-semibold text-neutral-800 mb-4">➕ 提供线索</h3>
                <form action="{{ route('lost-pets.clues.store', $lostPet) }}" method="POST" enctype="multipart/form-data" id="clue-form">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="block text-sm text-neutral-700 mb-1">在地图上点击标注位置 *</label>
                        <div id="clue-map" class="w-full h-[200px] rounded-lg overflow-hidden mb-2"></div>
                        <input type="hidden" name="lat" id="clue-lat" value="{{ old('lat') }}">
                        <input type="hidden" name="lng" id="clue-lng" value="{{ old('lng') }}">
                        @error('lat')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @error('lng')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm text-neutral-700 mb-1">地址（可选）</label>
                        <input type="text" name="address" value="{{ old('address') }}" 
                               placeholder="输入详细地址" 
                               class="input-field text-sm">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm text-neutral-700 mb-1">看到时间 *</label>
                        <input type="datetime-local" name="seen_at" value="{{ old('seen_at', date('Y-m-d\TH:i')) }}" 
                               class="input-field text-sm" required>
                        @error('seen_at')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm text-neutral-700 mb-1">描述（可选）</label>
                        <textarea name="description" rows="3" placeholder="描述看到的情况..." 
                                  class="input-field text-sm">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm text-neutral-700 mb-1">照片（可选）</label>
                        <input type="file" name="photo" accept="image/*" class="input-field text-sm">
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center gap-2 text-sm text-neutral-700 cursor-pointer">
                            <input type="checkbox" name="is_private" value="1" {{ old('is_private', '1') ? 'checked' : '' }} 
                                   class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                            <span>设为隐私线索（仅发布人和版主可见）</span>
                        </label>
                        <p class="text-xs text-neutral-500 mt-1">包含个人住址等敏感信息时建议开启</p>
                    </div>

                    <button type="submit" class="btn-primary w-full">
                        提交线索
                    </button>
                </form>
            </div>
        @endif

        @if($lostPet->status !== 'lost')
            <div class="card text-center py-6">
                <div class="text-4xl mb-2">{{ $lostPet->status === 'found' ? '🎉' : '📦' }}</div>
                <p class="text-neutral-600">{{ $lostPet->status === 'found' ? '宠物已找到，感谢大家的帮助！' : '该帖子已关闭' }}</p>
            </div>
        @elseif(!auth()->check())
            <div class="card text-center py-6">
                <div class="text-4xl mb-2">🔐</div>
                <p class="text-neutral-600 mb-3">登录后可以提供线索</p>
                <a href="{{ route('login') }}" class="btn-primary">登录</a>
            </div>
        @endif

        <div class="card">
            <h3 class="font-semibold text-neutral-800 mb-3">💡 温馨提示</h3>
            <ul class="text-sm text-neutral-600 space-y-2">
                <li class="flex items-start gap-2">
                    <span>•</span>
                    <span>看到走失宠物请及时提供线索</span>
                </li>
                <li class="flex items-start gap-2">
                    <span>•</span>
                    <span>提供真实信息，帮助宠物早日回家</span>
                </li>
                <li class="flex items-start gap-2">
                    <span>•</span>
                    <span>隐私线索将受到保护，仅相关人员可见</span>
                </li>
                <li class="flex items-start gap-2">
                    <span>•</span>
                    <span>找到宠物后请及时标记，方便大家了解</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<div id="mark-found-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">🎉 宠物已找到！</h3>
        <form action="{{ route('lost-pets.mark-found', $lostPet) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm text-neutral-700 mb-1">感谢留言（可选）</label>
                <textarea name="thank_you_note" rows="3" placeholder="感谢帮助过你的人..." 
                          class="input-field text-sm"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="hideMarkFoundModal()" class="btn-secondary flex-1">取消</button>
                <button type="submit" class="btn-primary flex-1">确认</button>
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
        0% { transform: scale(1); opacity: 0.7; }
        50% { transform: scale(2); opacity: 0; }
        100% { transform: scale(1); opacity: 0; }
    }
    .clue-marker .marker-pin {
        width: 24px;
        height: 24px;
    }
    .clue-marker .marker-pin span {
        font-size: 12px;
    }
    .modal-active {
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pet = @json($lostPet);
    const clues = @json($clues);
    const canViewPrivate = @json($canViewPrivate);

    const detailMap = L.map('detail-map').setView([pet.last_seen_lat, pet.last_seen_lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(detailMap);

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
            iconAnchor: isClue ? [12, 24] : [16, 32]
        });
    }

    const petColor = getMarkerColor(pet.status, pet.pet_type);
    const petEmoji = getPetEmoji(pet.pet_type);
    const petMarker = L.marker([pet.last_seen_lat, pet.last_seen_lng], {
        icon: createCustomMarker(petColor, petEmoji)
    }).addTo(detailMap);

    petMarker.bindPopup(`
        <div class="p-2">
            <div class="font-semibold text-sm">📍 最后出现地点</div>
            <div class="text-xs text-neutral-600">${pet.last_seen_address}</div>
        </div>
    `);

    clues.forEach((clue, index) => {
        if (clue.lat && clue.lng) {
            const isVisible = !clue.is_private || canViewPrivate;
            const clueColor = clue.is_verified ? '#10b981' : '#3b82f6';
            const clueMarker = L.marker([clue.lat, clue.lng], {
                icon: createCustomMarker(clueColor, '📍', true)
            }).addTo(detailMap);

            const description = isVisible ? (clue.description || '').substring(0, 50) : '隐私线索';
            clueMarker.bindPopup(`
                <div class="p-2">
                    <div class="font-semibold text-sm">💡 线索 ${index + 1}</div>
                    <div class="text-xs text-neutral-600">${clue.address || '已标记位置'}</div>
                    ${clue.is_verified ? '<span class="text-xs text-green-600">✅ 已核实</span>' : ''}
                    ${clue.is_private ? '<span class="text-xs text-amber-600 ml-1">🔒 隐私</span>' : ''}
                </div>
            `);
        }
    });

    const clueMap = document.getElementById('clue-map');
    if (clueMap) {
        const clueInputMap = L.map('clue-map').setView([pet.last_seen_lat, pet.last_seen_lng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(clueInputMap);

        L.marker([pet.last_seen_lat, pet.last_seen_lng]).addTo(clueInputMap).bindPopup('最后出现地点');

        let selectedMarker = null;
        clueInputMap.on('click', function(e) {
            if (selectedMarker) {
                clueInputMap.removeLayer(selectedMarker);
            }
            selectedMarker = L.marker(e.latlng, {
                icon: createCustomMarker('#3b82f6', '📍', true)
            }).addTo(clueInputMap);

            document.getElementById('clue-lat').value = e.latlng.lat.toFixed(7);
            document.getElementById('clue-lng').value = e.latlng.lng.toFixed(7);

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}&zoom=18&addressdetails=1`)
                .then(res => res.json())
                .then(data => {
                    if (data.display_name && !document.querySelector('input[name="address"]').value) {
                        document.querySelector('input[name="address"]').value = data.display_name;
                    }
                })
                .catch(() => {});
        });

        const latValue = document.getElementById('clue-lat').value;
        const lngValue = document.getElementById('clue-lng').value;
        if (latValue && lngValue) {
            selectedMarker = L.marker([parseFloat(latValue), parseFloat(lngValue)], {
                icon: createCustomMarker('#3b82f6', '📍', true)
            }).addTo(clueInputMap);
        }
    }

    const form = document.getElementById('clue-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const lat = document.getElementById('clue-lat').value;
            const lng = document.getElementById('clue-lng').value;
            if (!lat || !lng) {
                e.preventDefault();
                alert('请在地图上点击标注线索位置');
                return false;
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
});

function showMarkFoundModal() {
    document.getElementById('mark-found-modal').classList.remove('hidden');
    document.getElementById('mark-found-modal').classList.add('flex');
    document.body.classList.add('modal-active');
}

function hideMarkFoundModal() {
    document.getElementById('mark-found-modal').classList.add('hidden');
    document.getElementById('mark-found-modal').classList.remove('flex');
    document.body.classList.remove('modal-active');
}

document.getElementById('mark-found-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideMarkFoundModal();
    }
});
</script>
@endpush
