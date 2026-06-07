@extends('layouts.app')

@section('title', '版本历史 - ' . $topic->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('topics.show', $topic) }}" class="text-primary-600 hover:text-primary-700 mb-4 inline-block">← 返回公告详情</a>
    <h1 class="text-2xl font-bold text-gray-800">版本历史</h1>
    <p class="text-gray-600">{{ $topic->title }}</p>
</div>

<div class="relative">
    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

    @foreach($versions as $version)
    <div class="relative pl-12 pb-8">
        <div class="absolute left-2 w-5 h-5 rounded-full bg-white border-4 border-primary-600"></div>
        <div class="card">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-bold text-sm">
                        v{{ $version->version_number }}
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $version->title }}</h3>
                        <p class="text-sm text-gray-500">
                            修改者：{{ $version->user->username }}
                            ｜ {{ $version->created_at->format('Y-m-d H:i') }}
                        </p>
                    </div>
                </div>
                @if($version->change_summary)
                    <span class="text-sm bg-gray-100 text-gray-700 px-3 py-1 rounded">
                        {{ $version->change_summary }}
                    </span>
                @endif
            </div>

            @if($loop->iteration == 1 && $version->diff_from_previous)
                <div class="border-t border-gray-200 pt-3 mt-3">
                    <p class="text-sm font-medium text-gray-700 mb-2">与上一版本对比：</p>
                    @if($version->diff_from_previous['title'])
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 mb-1">标题变更：</p>
                            <div class="flex flex-col gap-1 text-sm">
                                <span class="text-red-600 line-through">旧：{{ $version->diff_from_previous['title']['old'] }}</span>
                                <span class="text-green-600">新：{{ $version->diff_from_previous['title']['new'] }}</span>
                            </div>
                        </div>
                    @endif
                    @if($version->diff_from_previous['content'])
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 mb-1">内容变更：</p>
                            <div class="grid md:grid-cols-2 gap-3">
                                <div class="bg-red-50 border border-red-200 rounded p-3">
                                <p class="text-xs text-red-700 font-medium mb-1">旧内容</p>
                                <div class="text-sm text-red-600 whitespace-pre-wrap">{{ $version->diff_from_previous['content']['old'] }}</div>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded p-3">
                                <p class="text-xs text-green-700 font-medium mb-1">新内容</p>
                                <div class="text-sm text-green-600 whitespace-pre-wrap">{{ $version->diff_from_previous['content']['new'] }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-3">
                <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $version->content }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
