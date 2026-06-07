@extends('layouts.app')

@section('title', $topic->title)

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 mb-4">
        @if($topic->is_pinned)
            <span class="text-xs bg-primary-100 text-primary-700 px-2 py-1 rounded">置顶</span>
        @endif
        @if($topic->is_property_notice)
            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded">
                @if($topic->notice_type == 'water')停水通知
                @elseif($topic->notice_type == 'elevator')电梯检修
                @elseif($topic->notice_type == 'fire')消防演练
                @else物业公告
                @endif
            </span>
            @if($isRead)
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">✓ 已读</span>
            @else
                <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">● 未读</span>
            @endif
        @else
            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">{{ category_name($topic->category) }}</span>
        @endif
    </div>
    <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ $topic->title }}</h1>
    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6">
        <span>发布方：{{ $topic->user->username }}</span>
        <span>发布时间：{{ $topic->created_at->format('Y-m-d H:i') }}</span>
        <span>浏览：{{ $topic->view_count }}</span>
        <span>回复：{{ $topic->reply_count }}</span>
        @if($topic->is_property_notice && $readStats)
            <span>已读：{{ $readStats['read_count'] }}/{{ $readStats['total_recipients'] }} ({{ $readStats['read_rate'] }}%)</span>
        @endif
        @auth
            @if($topic->is_property_notice)
                @if($topic->user_id === auth()->id() || auth()->user()->isAdmin())
                    <a href="{{ route('property-notices.edit', $topic) }}" class="text-primary-600 hover:text-primary-700">编辑公告</a>
                    <a href="{{ route('property-notices.read-receipts', $topic) }}" class="text-primary-600 hover:text-primary-700">查看已读</a>
                    <a href="{{ route('property-notices.phone-reminders', $topic) }}" class="text-orange-600 hover:text-orange-700">电话提醒</a>
                    <a href="{{ route('property-notices.version-history', $topic) }}" class="text-gray-600 hover:text-gray-700">版本历史</a>
                @endif
            @else
                @if($topic->user_id === auth()->id())
                    <a href="{{ route('topics.edit', $topic) }}" class="text-primary-600 hover:text-primary-700">编辑</a>
                    <form method="POST" action="{{ route('topics.destroy', $topic) }}" class="inline" data-confirm-delete>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-700">删除</button>
                    </form>
                @endif
            @endif
        @endauth
    </div>
</div>

@if($topic->is_property_notice && $latestDiff)
<div class="card mb-6 border-l-4 border-yellow-400 bg-yellow-50">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <h3 class="font-medium text-yellow-800 mb-2">公告已更新（版本 {{ $latestDiff['version2']->version_number }}）</h3>
            <p class="text-sm text-yellow-700 mb-2">
                修改时间：{{ $latestDiff['version2']->created_at->format('Y-m-d H:i') }}
                @if($latestDiff['version2']->change_summary)
                    ｜ 修改说明：{{ $latestDiff['version2']->change_summary }}
                @endif
            </p>
            @if($latestDiff['diff']['title'])
            <div class="mb-2">
                <p class="text-sm font-medium text-yellow-800">标题变更：</p>
                <p class="text-sm text-yellow-700 line-through">旧：{{ $latestDiff['diff']['title']['old'] }}</p>
                <p class="text-sm text-yellow-800 font-medium">新：{{ $latestDiff['diff']['title']['new'] }}</p>
            </div>
            @endif
            @if($latestDiff['diff']['content'])
            <div class="mb-2">
                <p class="text-sm font-medium text-yellow-800">内容变更：</p>
                <div class="text-sm text-yellow-700 bg-yellow-100 p-2 rounded mb-1">
                    <span class="font-medium">旧内容：</span><br>
                    {{ \Illuminate\Support\Str::limit($latestDiff['diff']['content']['old'], 200) }}
                </div>
                <div class="text-sm text-yellow-800 bg-white p-2 rounded border border-yellow-300">
                    <span class="font-medium">新内容：</span><br>
                    {{ \Illuminate\Support\Str::limit($latestDiff['diff']['content']['new'], 200) }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<div class="card mb-6">
    <div class="prose max-w-none">
        <p class="whitespace-pre-wrap text-gray-700">{{ $topic->content }}</p>
    </div>
</div>

@if($topic->is_property_notice && $readStats)
<div class="card mb-6 bg-gray-50">
    <h3 class="font-medium text-gray-800 mb-3">阅读统计</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $readStats['total_recipients'] }}</p>
            <p class="text-sm text-gray-600">接收人数</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-green-600">{{ $readStats['read_count'] }}</p>
            <p class="text-sm text-gray-600">已读人数</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-red-600">{{ $readStats['unread_count'] }}</p>
            <p class="text-sm text-gray-600">未读人数</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $readStats['read_rate'] }}%</p>
            <p class="text-sm text-gray-600">阅读率</p>
        </div>
    </div>
</div>
@endif

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">回复 ({{ $topic->replies->count() }})</h2>
    
    @auth
        <div class="card mb-6">
            <form method="POST" action="{{ route('replies.store', $topic) }}">
                @csrf
                <div class="mb-4">
                    <label for="content" class="block text-gray-700 text-sm font-medium mb-2">发表回复</label>
                    <textarea id="content" name="content" rows="4" required
                              class="input-field @error('content') border-red-500 @enderror"
                              placeholder="请输入回复内容..."></textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary">发表回复</button>
            </form>
        </div>
    @else
        <div class="card mb-6 text-center py-4">
            <p class="text-gray-600">请 <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700">登录</a> 后发表回复</p>
        </div>
    @endauth

    <div class="space-y-4">
        @forelse($topic->replies as $reply)
            <div class="card">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-medium text-gray-800">{{ $reply->user->username }}</span>
                            <span class="text-sm text-gray-500">{{ $reply->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $reply->content }}</p>
                    </div>
                    @auth
                        @if($reply->user_id === auth()->id())
                            <form method="POST" action="{{ route('replies.destroy', $reply) }}" class="ml-4" data-confirm-delete>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700 text-sm">删除</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        @empty
            <div class="card text-center py-8">
                <p class="text-gray-500">暂无回复</p>
            </div>
        @endforelse
    </div>
</div>

<div class="mb-4">
    <a href="{{ route('topics.index') }}" class="text-primary-600 hover:text-primary-700">← 返回主题列表</a>
</div>
@endsection
