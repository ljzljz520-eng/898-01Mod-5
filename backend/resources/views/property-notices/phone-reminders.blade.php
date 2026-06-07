@extends('layouts.app')

@section('title', '电话提醒名单 - ' . $topic->title)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <a href="{{ route('topics.show', $topic) }}" class="text-primary-600 hover:text-primary-700 mb-2 inline-block">← 返回公告详情</a>
            <h1 class="text-2xl font-bold text-gray-800">电话提醒名单</h1>
            <p class="text-gray-600">{{ $topic->title }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('property-notices.read-receipts', $topic) }}" class="btn-secondary">已读回执</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-orange-50 border-l-4 border-orange-400">
            <p class="text-3xl font-bold text-orange-600">{{ $reminderList->count() }}</p>
            <p class="text-sm text-orange-700">需要电话提醒</p>
        </div>
        <div class="card bg-red-50 border-l-4 border-red-400">
            <p class="text-3xl font-bold text-red-600">{{ $elderlyCount }}</p>
            <p class="text-sm text-red-700">其中老人住户</p>
        </div>
        <div class="card bg-gray-50 border-l-4 border-gray-400">
            <p class="text-3xl font-bold text-gray-600">{{ $unreadDays }}天</p>
            <p class="text-sm text-gray-700">未读时间阈值</p>
        </div>
    </div>

    <div class="card mb-6">
        <form method="GET" action="{{ route('property-notices.phone-reminders', $topic) }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-gray-700 font-medium mb-1">未读天数阈值</label>
                <select name="unread_days" class="input-field" onchange="this.form.submit()">
                    <option value="1" {{ $unreadDays == 1 ? 'selected' : '' }}>超过 1 天未读</option>
                    <option value="2" {{ $unreadDays == 2 ? 'selected' : '' }}>超过 2 天未读</option>
                    <option value="3" {{ $unreadDays == 3 ? 'selected' : '' }}>超过 3 天未读</option>
                    <option value="5" {{ $unreadDays == 5 ? 'selected' : '' }}>超过 5 天未读</option>
                    <option value="7" {{ $unreadDays == 7 ? 'selected' : '' }}>超过 7 天未读</option>
                </select>
            </div>
            <div>
                <a href="#" class="btn-primary" onclick="exportReminders(); return false;">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    导出名单
                </a>
            </div>
        </form>
    </div>

    @if($reminderList->count() > 0)
    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-medium text-gray-600">序号</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">住户姓名</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">电话</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">住址</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">是否老人</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">未读天数</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reminderList as $index => $item)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4 text-gray-600">{{ $index + 1 }}</td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-800">{{ $item['username'] }}</span>
                            @if($item['is_elderly'])
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">重点关注</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <a href="tel:{{ $item['phone'] }}" class="text-primary-600 hover:text-primary-700 font-mono">
                            {{ $item['phone'] }}
                        </a>
                    </td>
                    <td class="py-3 px-4 text-gray-600">{{ $item['address'] }}</td>
                    <td class="py-3 px-4">
                        @if($item['is_elderly'])
                            <span class="text-red-600 font-medium">是</span>
                        @else
                            <span class="text-gray-400">否</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        @if($item['unread_days'] >= 5)
                            <span class="text-red-600 font-medium">{{ $item['unread_days'] }} 天</span>
                        @elseif($item['unread_days'] >= 3)
                            <span class="text-orange-600 font-medium">{{ $item['unread_days'] }} 天</span>
                        @else
                            <span class="text-yellow-600">{{ $item['unread_days'] }} 天</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <a href="tel:{{ $item['phone'] }}" class="inline-flex items-center gap-1 px-3 py-1 bg-primary-100 text-primary-700 rounded text-sm hover:bg-primary-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            拨打电话
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card mt-6 bg-yellow-50 border border-yellow-200">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="font-medium text-yellow-800">工作建议</p>
                <p class="text-sm text-yellow-700 mt-1">
                    1. 优先联系标记为「重点关注」的老人住户，确保其生活不受影响<br>
                    2. 电话联系时请说明公告的主要内容，确认住户已了解相关信息<br>
                    3. 对于无法联系到的住户，请安排物业人员上门通知
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="card text-center py-12">
        <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-gray-600 text-lg">太好了！当前没有需要电话提醒的住户</p>
        <p class="text-gray-500 text-sm mt-2">所有住户都已阅读公告或不符合提醒条件</p>
    </div>
    @endif
</div>

<script>
function exportReminders() {
    const unreadDays = {{ $unreadDays }};
    window.location.href = `/api/property-notices/{{ $topic->id }}/export-reminders?unread_days=${unreadDays}`;
}
</script>
@endsection
