<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PetClueRequest;
use App\Models\LostPet;
use App\Models\PetClue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetClueController extends Controller
{
    public function index(Request $request, LostPet $lostPet): JsonResponse
    {
        $user = $request->user();
        $canViewPrivate = $lostPet->canViewPrivateClues($user);

        $query = $lostPet->clues()->with('user')->orderBy('seen_at', 'desc');

        if (!$canViewPrivate) {
            $query->where('is_private', false);
        }

        $clues = $query->paginate($request->get('per_page', 20));

        $clues->getCollection()->transform(function ($clue) use ($user) {
            if ($clue->is_private && !$clue->canView($user)) {
                $clue->address = '（仅发布人和版主可见）';
                $clue->lat = null;
                $clue->lng = null;
                $clue->description = '（该线索包含隐私信息，仅发布人和版主可见）';
            }
            return $clue;
        });

        return response()->json([
            'data' => $clues->items(),
            'meta' => [
                'current_page' => $clues->currentPage(),
                'per_page' => $clues->perPage(),
                'total' => $clues->total(),
                'last_page' => $clues->lastPage(),
            ],
        ]);
    }

    public function store(PetClueRequest $request, LostPet $lostPet): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未认证'], 401);
        }

        if ($lostPet->status !== 'lost') {
            return response()->json(['message' => '该宠物已找到或已关闭，无法添加线索'], 400);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('pet_clues', 'public');
        }

        $clue = PetClue::create([
            'lost_pet_id' => $lostPet->id,
            'user_id' => $user->id,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'address' => $request->address,
            'seen_at' => $request->seen_at,
            'description' => $request->description,
            'photo_path' => $photoPath,
            'is_private' => $request->is_private ?? true,
        ]);

        $lostPet->increment('clue_count');
        $clue->load('user');

        return response()->json([
            'data' => $clue,
            'message' => '线索提交成功，感谢您的帮助！',
        ], 201);
    }

    public function update(PetClueRequest $request, PetClue $clue): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未认证'], 401);
        }

        if (!$clue->canEdit($user)) {
            return response()->json(['message' => '无权限操作'], 403);
        }

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            if ($clue->photo_path) {
                Storage::disk('public')->delete($clue->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('pet_clues', 'public');
        }

        $clue->update($data);
        $clue->load('user');

        return response()->json([
            'data' => $clue,
            'message' => '更新成功',
        ]);
    }

    public function destroy(Request $request, PetClue $clue): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未认证'], 401);
        }

        if (!$clue->canEdit($user)) {
            return response()->json(['message' => '无权限操作'], 403);
        }

        if ($clue->photo_path) {
            Storage::disk('public')->delete($clue->photo_path);
        }

        $clue->lostPet->decrement('clue_count');
        $clue->delete();

        return response()->json(['message' => '删除成功']);
    }

    public function verify(Request $request, PetClue $clue): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未认证'], 401);
        }

        if ($clue->lostPet->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => '无权限操作'], 403);
        }

        $clue->verify();

        return response()->json([
            'data' => $clue,
            'message' => '线索已核实',
        ]);
    }
}
