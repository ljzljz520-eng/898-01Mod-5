@extends('layouts.app')

@section('title', '已读回执 - ' . $topic->title)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <a href="{{ route('topics.show', $topic) }}" class="text-primary-600 hover:text-primary-700 mb-2 inline-block">← 返回公告详情</a>
            <h1 class="text-2xl font-bold text-gray-800">已读回执管理</h1>
            <p class="text-gray-600">{{ $topic->title }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('property-notices.phone-reminders', $topic) }}" class="btn-primary">电话提醒名单</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="card bg-blue-50">
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_recipients'] }}</p>
            <p class="text-sm text-blue-700">接收人数</p>
        </div>
        <div class="card bg-green-50">
            <p class="text-3xl font-bold text-green-600">{{ $stats['read_count'] }}</p>
            <p class="text-sm text-green-700">已读人数</p>
        </div>
        <div class="card bg-red-50">
            <p class="text-3xl font-bold text-red-600">{{ $stats['unread_count'] }}</p>
            <p class="text-sm text-red-700">未读人数</p>
        </div>
        <div class="card bg-purple-50">
            <p class="text-3xl font-bold text-purple-600">{{ $stats['read_rate'] }}%</p>
            <p class="text-sm text-purple-700">阅读率</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('property-notices.read-receipts', ['topic' => $topic, 'status' => 'all']) }}" 
           class="px-4 py-2 rounded text-sm {{ $status == 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            全部 ({{ $stats['total_recipients'] }})
        </a>
        <a href="{{ route('property-notices.read-receipts', ['topic' => $topic, 'status' => 'read']) }}" 
           class="px-4 py-2 rounded text-sm {{ $status == 'read' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            已读 ({{ $stats['read_count'] }})
        </a>
        <a href="{{ route('property-notices.read-receipts', ['topic' => $topic, 'status' => 'unread']) }}" 
           class="px-4 py-2 rounded text-sm {{ $status == 'unread' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            未读 ({{ $stats['unread_count'] }})
        </a>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-medium text-gray-600">住户信息</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">住址</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">电话</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">是否老人</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">状态</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">阅读时间</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts as $receipt)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <div class="font-medium text-gray-800">{{ $receipt->user->username }}</div>
                        @if($receipt->user->is_elderly)
                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded">老人</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-gray-600">
                        {{ $receipt->user->building ?? '-' }}
                        @if($receipt->user->unit)号楼 {{ $receipt->user->unit }}单元 @endif
                        @if($receipt->user->room_number){{ $receipt->user->room_number }}室 @endif
                    </td>
                    <td class="py-3 px-4 text-gray-600">
                        {{ $receipt->user->phone ?? '-' }}
                    </td>
                    <td class="py-3 px-4">
                        @if($receipt->user->is_elderly)
                            <span class="text-orange-600">是</span>
                        @else
                            <span class="text-gray-400">否</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        @if($receipt->read_at)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-green-100 text-green-700">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                已读
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-red-100 text-red-700">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                未读
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-gray-500">
                        @if($receipt->read_at)
                            {{ $receipt->read_at->format('Y-m-d H:i') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500">
                        暂无数据
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($receipts->hasPages())
        <div class="mt-4">
            {{ $receipts->appends(request()->query())->links('pagination.custom') }}
        </div>
        @endif
    </div>
</div>
@endsection
