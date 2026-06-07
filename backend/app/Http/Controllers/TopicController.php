<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopicRequest;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $type = $request->get('type', 'all');

        $query = Topic::with('user')
            ->where('status', 1)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc');

        if ($type === 'notice') {
            $query->where('is_property_notice', true);
        } elseif ($type === 'post') {
            $query->where('is_property_notice', false);
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('notice_type') && $request->notice_type !== 'all') {
            $query->where('notice_type', $request->notice_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $topics = $query->paginate(20)->appends(request()->query());

        if (auth()->check()) {
            $topics->getCollection()->each(function ($topic) {
                if ($topic->is_property_notice) {
                    $topic->is_read = $topic->isReadBy(auth()->user());
                }
            });
        }

        return view('topics.index', compact('topics', 'type'));
    }

    public function show(Topic $topic)
    {
        $topic->increment('view_count');
        $topic->load(['user', 'replies' => function($query) {
            $query->orderBy('created_at', 'asc');
        }, 'replies.user']);

        $isRead = false;
        if (auth()->check() && $topic->is_property_notice) {
            $receipt = $topic->markAsRead(
                auth()->user(),
                request()->ip(),
                request()->userAgent()
            );
            $isRead = $receipt && $receipt->read_at ? true : false;
        }

        $readStats = null;
        $latestDiff = null;
        if ($topic->is_property_notice) {
            $readStats = [
                'read_count' => $topic->read_count,
                'unread_count' => $topic->unread_count,
                'total_recipients' => $topic->total_recipients,
                'read_rate' => $topic->read_rate,
            ];

            $lastTwoVersions = $topic->versions()->take(2)->get();
            if ($lastTwoVersions->count() >= 2) {
                $latestDiff = $topic->getVersionDiff(
                    $lastTwoVersions[1]->version_number,
                    $lastTwoVersions[0]->version_number
                );
            }
        }

        return view('topics.show', compact('topic', 'isRead', 'readStats', 'latestDiff'));
    }

    public function create()
    {
        return view('topics.create');
    }

    public function store(TopicRequest $request)
    {
        $topic = Topic::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category ?? 'general',
        ]);

        return redirect()->route('topics.show', $topic)->with('success', '发布成功');
    }

    public function edit(Topic $topic)
    {
        if ($topic->user_id !== auth()->id()) {
            abort(403, '无权限操作');
        }

        return view('topics.edit', compact('topic'));
    }

    public function update(TopicRequest $request, Topic $topic)
    {
        if ($topic->user_id !== auth()->id()) {
            abort(403, '无权限操作');
        }

        $topic->update([
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category ?? $topic->category,
        ]);

        return redirect()->route('topics.show', $topic)->with('success', '更新成功');
    }

    public function destroy(Topic $topic)
    {
        if ($topic->user_id !== auth()->id()) {
            abort(403, '无权限操作');
        }

        $topic->delete();

        return redirect()->route('topics.index')->with('success', '删除成功');
    }
}
