@extends('layouts.app')

@section('title', '宠物走失寻回')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-neutral-800">🐾 宠物走失寻回</h1>
            <p class="text-sm text-neutral-500 mt-1">帮助社区居民找回走失的宠物</p>
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

<div class="mb-6 flex flex-col sm:flex-row gap-3">
    <form method="GET" action="{{ route('lost-pets.index') }}" class="flex-1 flex flex-wrap gap-2 items-center">
        <input type="text" name="search" value="{{ request('search') }}" 
               placeholder="搜索宠物名、品种..." 
               class="flex-1 min-w-[200px] input-field">
        <select name="status" class="input-field w-auto text-sm">
            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>全部状态</option>
            <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>走失中</option>
            <option value="found" {{ request('status') == 'found' ? 'selected' : '' }}>已找到</option>
            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>已关闭</option>
        </select>
        <select name="pet_type" class="input-field w-auto text-sm">
            <option value="all" {{ request('pet_type') == 'all' ? 'selected' : '' }}>全部类型</option>
            <option value="dog" {{ request('pet_type') == 'dog' ? 'selected' : '' }}>🐕 狗狗</option>
            <option value="cat" {{ request('pet_type') == 'cat' ? 'selected' : '' }}>🐱 猫咪</option>
            <option value="other" {{ request('pet_type') == 'other' ? 'selected' : '' }}>🐾 其他</option>
        </select>
        <button type="submit" class="btn-secondary text-sm px-4">搜索</button>
        <a href="{{ route('lost-pets.map') }}" class="btn-secondary text-sm px-4">
            🗺️ 地图视图
        </a>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($pets as $pet)
        <div class="card hover:shadow-md transition-shadow cursor-pointer" onclick="location.href='{{ route('lost-pets.show', $pet) }}'">
            <div class="aspect-video bg-neutral-100 rounded-md mb-3 overflow-hidden relative">
                @if($pet->photo_path)
                    <img src="{{ Storage::url($pet->photo_path) }}" alt="{{ $pet->pet_name ?? '宠物照片' }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-5xl text-neutral-300">
                        {{ $pet->pet_type === 'dog' ? '🐕' : ($pet->pet_type === 'cat' ? '🐱' : '🐾') }}
                    </div>
                @endif
                <div class="absolute top-2 right-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ pet_status_badge_class($pet->status) }}">
                        {{ pet_status_name($pet->status) }}
                    </span>
                </div>
            </div>
            
            <div class="flex items-center gap-2 mb-2">
                <span class="badge text-[11px]">{{ pet_type_name($pet->pet_type) }}</span>
                @if($pet->breed)
                    <span class="text-xs text-neutral-500">{{ $pet->breed }}</span>
                @endif
            </div>
            
            <h3 class="font-semibold text-neutral-800 mb-1">
                {{ $pet->pet_name ? $pet->pet_name . ' - ' : '' }}
                {{ $pet->color ?? '' }} {{ $pet->pet_type === 'dog' ? '狗狗' : ($pet->pet_type === 'cat' ? '猫咪' : '宠物') }}
            </h3>
            
            <div class="text-sm text-neutral-600 space-y-1">
                <p class="flex items-center gap-1">
                    <span>📍</span>
                    <span class="line-clamp-1">{{ $pet->last_seen_address }}</span>
                </p>
                <p class="flex items-center gap-1 text-xs text-neutral-500">
                    <span>⏰</span>
                    <span>最后出现：{{ $pet->last_seen_at->format('Y-m-d H:i') }}</span>
                </p>
            </div>
            
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-neutral-100 text-xs text-neutral-500">
                <span>发布者：{{ $pet->user->username }}</span>
                <div class="flex items-center gap-3">
                    <span>👁️ {{ $pet->view_count }}</span>
                    <span>💬 {{ $pet->clue_count }}</span>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full card text-center py-12">
            <div class="text-6xl mb-4">🐾</div>
            <p class="text-gray-500 text-lg">暂无走失宠物信息</p>
            @auth
                <a href="{{ route('lost-pets.create') }}" class="btn-primary mt-4">
                    发布第一条走失信息
                </a>
            @endauth
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $pets->appends(request()->query())->links('pagination.custom') }}
</div>
@endsection
