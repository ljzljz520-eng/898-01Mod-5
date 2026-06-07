<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopicRequest;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyNoticeWebController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        $buildings = $this->getBuildings();
        $noticeTypes = [
            'water' => '停水通知',
            'elevator' => '电梯检修',
            'fire' => '消防演练',
            'general' => '其他公告',
        ];

        return view('property-notices.create', compact('buildings', 'noticeTypes'));
    }

    public function store(TopicRequest $request)
    {
        DB::transaction(function () use ($request) {
            $topic = Topic::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'content' => $request->content,
                'category' => $request->notice_type ?? 'general',
                'is_property_notice' => true,
                'notice_type' => $request->notice_type ?? 'general',
                'is_pinned' => $request->boolean('is_pinned', false),
            ]);

            if ($request->has('recipients') && !empty($request->recipients)) {
                $topic->syncRecipients($request->recipients);
            } else {
                $topic->syncReadReceipts();
            }

            $topic->createVersion(auth()->id(), '公告发布');
        });

        return redirect()->route('topics.index')->with('success', '物业公告发布成功');
    }

    public function edit(Topic $topic)
    {
        if (!auth()->user()->isAdmin() && $topic->user_id !== auth()->id()) {
            abort(403, '无权限操作');
        }

        if (!$topic->is_property_notice) {
            abort(404, '该公告不是物业公告');
        }

        $buildings = $this->getBuildings();
        $noticeTypes = [
            'water' => '停水通知',
            'elevator' => '电梯检修',
            'fire' => '消防演练',
            'general' => '其他公告',
        ];

        $selectedRecipients = $topic->recipients->map(function ($r) {
            return [
                'type' => $r->recipient_type,
                'value' => $r->recipient_value,
                'label' => $this->getRecipientLabel($r->recipient_type, $r->recipient_value),
            ];
        });

        return view('property-notices.edit', compact('topic', 'buildings', 'noticeTypes', 'selectedRecipients'));
    }

    public function update(TopicRequest $request, Topic $topic)
    {
        if (!auth()->user()->isAdmin() && $topic->user_id !== auth()->id()) {
            abort(403, '无权限操作');
        }

        if (!$topic->is_property_notice) {
            abort(404, '该公告不是物业公告');
        }

        DB::transaction(function () use ($request, $topic) {
            $topic->update([
                'title' => $request->title,
                'content' => $request->content,
                'notice_type' => $request->notice_type ?? $topic->notice_type,
                'is_pinned' => $request->boolean('is_pinned', $topic->is_pinned),
            ]);

            if ($request->has('recipients') && !empty($request->recipients)) {
                $topic->syncRecipients($request->recipients);
            }

            $topic->createVersion(auth()->id(), $request->change_summary ?? '内容更新');
        });

        return redirect()->route('topics.show', $topic)->with('success', '物业公告更新成功');
    }

    public function readReceipts(Request $request, Topic $topic)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, '无权限操作');
        }

        if (!$topic->is_property_notice) {
            abort(404, '该公告不是物业公告');
        }

        $status = $request->get('status', 'all');
        $query = $topic->readReceipts()->with('user');

        if ($status === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($status === 'unread') {
            $query->whereNull('read_at');
        }

        $receipts = $query->paginate(20)->appends(request()->query());

        $stats = [
            'read_count' => $topic->read_count,
            'unread_count' => $topic->unread_count,
            'total_recipients' => $topic->total_recipients,
            'read_rate' => $topic->read_rate,
        ];

        return view('property-notices.read-receipts', compact('topic', 'receipts', 'status', 'stats'));
    }

    public function phoneReminders(Request $request, Topic $topic)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, '无权限操作');
        }

        if (!$topic->is_property_notice) {
            abort(404, '该公告不是物业公告');
        }

        $unreadDays = $request->get('unread_days', 3);
        $reminderList = $topic->getPhoneReminderList($unreadDays);

        $elderlyCount = $reminderList->where('is_elderly', true)->count();

        return view('property-notices.phone-reminders', compact('topic', 'reminderList', 'unreadDays', 'elderlyCount'));
    }

    public function versionHistory(Topic $topic)
    {
        if (!$topic->is_property_notice) {
            abort(404, '该公告不是物业公告');
        }

        $versions = $topic->versions()->with('user')->get();

        return view('property-notices.version-history', compact('topic', 'versions'));
    }

    protected function getBuildings()
    {
        return User::whereNotNull('building')
            ->distinct()
            ->orderBy('building')
            ->pluck('building')
            ->map(function ($building) {
                $units = User::where('building', $building)
                    ->whereNotNull('unit')
                    ->distinct()
                    ->orderBy('unit')
                    ->pluck('unit')
                    ->toArray();
                return [
                    'name' => $building,
                    'units' => $units,
                ];
            });
    }

    protected function getRecipientLabel($type, $value)
    {
        switch ($type) {
            case 'building':
                return $value . ' 全体住户';
            case 'unit':
                return str_replace('-', '号楼 ', $value) . '单元 全体住户';
            case 'user':
                $user = User::find($value);
                return $user ? $user->username : '未知用户';
            default:
                return $value;
        }
    }
}
