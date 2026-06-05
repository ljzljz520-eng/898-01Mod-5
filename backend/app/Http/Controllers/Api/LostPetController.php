<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LostPetRequest;
use App\Models\LostPet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LostPetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LostPet::with('user')->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('pet_type') && $request->pet_type !== 'all') {
            $query->where('pet_type', $request->pet_type);
        }

        if ($request->has('lat') && $request->has('lng')) {
            $radius = $request->get('radius', 10);
            $query->nearby($request->lat, $request->lng, $radius);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pet_name', 'like', "%{$search}%")
                  ->orWhere('breed', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('last_seen_address', 'like', "%{$search}%");
            });
        }

        $pets = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $pets->items(),
            'meta' => [
                'current_page' => $pets->currentPage(),
                'per_page' => $pets->perPage(),
                'total' => $pets->total(),
                'last_page' => $pets->lastPage(),
            ],
        ]);
    }

    public function mapMarkers(Request $request): JsonResponse
    {
        $query = LostPet::select(['id', 'pet_name', 'pet_type', 'status', 'last_seen_lat', 'last_seen_lng', 'last_seen_address', 'last_seen_at', 'photo_path']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('pet_type') && $request->pet_type !== 'all') {
            $query->where('pet_type', $request->pet_type);
        }

        $pets = $query->get();

        return response()->json([
            'data' => $pets,
        ]);
    }

    public function show(LostPet $lostPet, Request $request): JsonResponse
    {
        $lostPet->increment('view_count');
        $lostPet->load(['user']);

        $user = $request->user();
        $canViewPrivate = $lostPet->canViewPrivateClues($user);

        $cluesQuery = $lostPet->clues()->with('user')->orderBy('seen_at', 'desc');

        if (!$canViewPrivate) {
            $cluesQuery->where('is_private', false);
        }

        $clues = $cluesQuery->get()->map(function ($clue) use ($user) {
            if ($clue->is_private && !$clue->canView($user)) {
                $clue->address = '（仅发布人和版主可见）';
                $clue->lat = null;
                $clue->lng = null;
                $clue->description = '（该线索包含隐私信息，仅发布人和版主可见）';
            }
            return $clue;
        });

        return response()->json([
            'data' => [
                'pet' => $lostPet,
                'clues' => $clues,
                'can_view_private' => $canViewPrivate,
                'is_owner' => $lostPet->isOwner($user),
            ],
        ]);
    }

    public function store(LostPetRequest $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未认证'], 401);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('lost_pets', 'public');
        }

        $lostPet = LostPet::create([
            'user_id' => $user->id,
            'pet_name' => $request->pet_name,
            'pet_type' => $request->pet_type,
            'breed' => $request->breed,
            'color' => $request->color,
            'collar_features' => $request->collar_features,
            'description' => $request->description,
            'photo_path' => $photoPath,
            'last_seen_lat' => $request->last_seen_lat,
            'last_seen_lng' => $request->last_seen_lng,
            'last_seen_address' => $request->last_seen_address,
            'last_seen_at' => $request->last_seen_at,
            'contact_phone' => $request->contact_phone,
            'contact_name' => $request->contact_name,
        ]);

        $lostPet->load('user');

        return response()->json([
            'data' => $lostPet,
            'message' => '发布成功',
        ], 201);
    }

    public function update(LostPetRequest $request, LostPet $lostPet): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未认证'], 401);
        }

        if ($lostPet->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => '无权限操作'], 403);
        }

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            if ($lostPet->photo_path) {
                Storage::disk('public')->delete($lostPet->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('lost_pets', 'public');
        }

        $lostPet->update($data);
        $lostPet->load('user');

        return response()->json([
            'data' => $lostPet,
            'message' => '更新成功',
        ]);
    }

    public function destroy(Request $request, LostPet $lostPet): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未认证'], 401);
        }

        if ($lostPet->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => '无权限操作'], 403);
        }

        if ($lostPet->photo_path) {
            Storage::disk('public')->delete($lostPet->photo_path);
        }

        $lostPet->delete();

        return response()->json(['message' => '删除成功']);
    }

    public function markFound(Request $request, LostPet $lostPet): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未认证'], 401);
        }

        if ($lostPet->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => '无权限操作'], 403);
        }

        $request->validate([
            'thank_you_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $lostPet->markAsFound($request->thank_you_note);

        return response()->json([
            'data' => $lostPet,
            'message' => '已标记为找到，感谢大家的帮助！',
        ]);
    }

    public function close(Request $request, LostPet $lostPet): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未认证'], 401);
        }

        if (!$user->isAdmin()) {
            return response()->json(['message' => '无权限操作'], 403);
        }

        $lostPet->close();

        return response()->json([
            'data' => $lostPet,
            'message' => '帖子已关闭',
        ]);
    }
}
