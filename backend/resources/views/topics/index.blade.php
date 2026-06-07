@extends('layouts.app')

@section('title', $type == 'notice' ? '物业公告' : '主题列表')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-neutral-800">
        @if($type == 'notice')物业公告
        @elseif($type == 'post')讨论帖子
        @else最新主题
        @endif
    </h1>
</div>

<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('topics.index', ['type' => 'all']) }}" 
       class="px-4 py-2 rounded text-sm {{ $type == 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
        全部
    </a>
    <a href="{{ route('topics.index', ['type' => 'notice']) }}" 
       class="px-4 py-2 rounded text-sm {{ $type == 'notice' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
        物业公告
    </a>
    <a href="{{ route('topics.index', ['type' => 'post']) }}" 
       class="px-4 py-2 rounded text-sm {{ $type == 'post' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
        讨论帖子
    </a>
</div>

<div class="mb-4 flex flex-col sm:flex-row gap-3">
    <form method="GET" action="{{ route('topics.index') }}" class="flex-1 flex flex-wrap gap-2 items-center" data-topic-filter>
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="text" name="search" value="{{ request('search') }}" 
               placeholder="搜索主题..." 
               class="flex-1 input-field">
        
        @if($type == 'notice')
            <select name="notice_type" class="input-field w-auto text-sm">
                <option value="all" {{ request('notice_type') == 'all' ? 'selected' : '' }}>全部公告</option>
                <option value="water" {{ request('notice_type') == 'water' ? 'selected' : '' }}>停水通知</option>
                <option value="elevator" {{ request('notice_type') == 'elevator' ? 'selected' : '' }}>电梯检修</option>
                <option value="fire" {{ request('notice_type') == 'fire' ? 'selected' : '' }}>消防演练</option>
                <option value="general" {{ request('notice_type') == 'general' ? 'selected' : '' }}>其他公告</option>
            </select>
        @else
            <select name="category" class="input-field w-auto text-sm">
                <option value="all" {{ request('category') == 'all' ? 'selected' : '' }}>全部分类</option>
                <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>综合讨论</option>
                <option value="tech" {{ request('category') == 'tech' ? 'selected' : '' }}>技术交流</option>
                <option value="study" {{ request('category') == 'study' ? 'selected' : '' }}>学习心得</option>
                <option value="question" {{ request('category') == 'question' ? 'selected' : '' }}>问题求助</option>
            </select>
        @endif
        
        <button type="submit" class="btn-secondary text-sm px-3">搜索</button>
    </form>
</div>

<div class="space-y-3" data-topic-list>
    @forelse($topics as $topic)
        <div class="card {{ $topic->is_property_notice ? 'border-l-4 border-orange-400' : '' }}">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        @if($topic->is_pinned)
                            <span class="badge-primary">置顶</span>
                        @endif
                        @if($topic->is_property_notice)
                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded">
                                @if($topic->notice_type == 'water')停水通知
                                @elseif($topic->notice_type == 'elevator')电梯检修
                                @elseif($topic->notice_type == 'fire')消防演练
                                @else物业公告
                                @endif
                            </span>
                            @auth
                                @if($topic->is_read)
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded">✓ 已读</span>
                                @else
                                    <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded">● 未读</span>
                                @endif
                            @endauth
                        @else
                            <span class="badge text-[11px]">{{ category_name($topic->category) }}</span>
                        @endif
                    </div>
                    <a href="{{ route('topics.show', $topic) }}" class="block text-base md:text-lg font-semibold text-neutral-800 hover:text-primary-600 mb-1">
                            {{ $topic->title }}
                    </a>
                    <p class="text-neutral-600 text-sm mb-2 line-clamp-2">{{ Str::limit($topic->content, 150) }}</p>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-neutral-500">
                        <span>{{ $topic->is_property_notice ? '发布方' : '作者' }}：{{ $topic->user->username }}</span>
                        <span>发布时间：{{ $topic->created_at->format('Y-m-d H:i') }}</span>
                        <span>浏览：{{ $topic->view_count }}</span>
                        @if(!$topic->is_property_notice)
                            <span>回复：{{ $topic->reply_count }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card text-center py-12">
            <p class="text-gray-500 text-lg">
                @if($type == 'notice')暂无物业公告
                @elseif($type == 'post')暂无讨论帖子
                @else暂无主题
                @endif
            </p>
        </div>
    @endforelse
</div>

<div class="mt-6" data-topic-pagination>
    {{ $topics->links('pagination.custom') }}
</div>
@endsection
