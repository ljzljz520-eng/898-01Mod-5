<?php

namespace App\Http\Controllers;

use App\Http\Requests\LostPetRequest;
use App\Http\Requests\PetClueRequest;
use App\Models\LostPet;
use App\Models\PetClue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LostPetController extends Controller
{
    public function index(Request $request)
    {
        $query = LostPet::with('user')->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('pet_type') && $request->pet_type !== 'all') {
            $query->where('pet_type', $request->pet_type);
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

        $pets = $query->paginate(12);

        return view('lost-pets.index', compact('pets'));
    }

    public function map(Request $request)
    {
        $status = $request->get('status', 'lost');
        $petType = $request->get('pet_type', 'all');

        return view('lost-pets.map', compact('status', 'petType'));
    }

    public function show(LostPet $lostPet)
    {
        $lostPet->increment('view_count');
        $lostPet->load(['user']);

        $user = auth()->user();
        $canViewPrivate = $lostPet->canViewPrivateClues($user);
        $isOwner = $lostPet->isOwner($user);

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

        return view('lost-pets.show', compact('lostPet', 'clues', 'canViewPrivate', 'isOwner'));
    }

    public function create()
    {
        return view('lost-pets.create');
    }

    public function store(LostPetRequest $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', '请先登录');
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

        return redirect()->route('lost-pets.show', $lostPet)->with('success', '发布成功！');
    }

    public function edit(LostPet $lostPet)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        if ($lostPet->user_id !== $user->id && !$user->isAdmin()) {
            return back()->with('error', '无权限操作');
        }

        return view('lost-pets.edit', compact('lostPet'));
    }

    public function update(LostPetRequest $request, LostPet $lostPet)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        if ($lostPet->user_id !== $user->id && !$user->isAdmin()) {
            return back()->with('error', '无权限操作');
        }

        $data = $request->except('photo', 'remove_photo');

        if ($request->has('remove_photo') && $request->remove_photo) {
            if ($lostPet->photo_path) {
                Storage::disk('public')->delete($lostPet->photo_path);
            }
            $data['photo_path'] = null;
        } elseif ($request->hasFile('photo')) {
            if ($lostPet->photo_path) {
                Storage::disk('public')->delete($lostPet->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('lost_pets', 'public');
        }

        $lostPet->update($data);

        return redirect()->route('lost-pets.show', $lostPet)->with('success', '更新成功！');
    }

    public function destroy(LostPet $lostPet)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        if ($lostPet->user_id !== $user->id && !$user->isAdmin()) {
            return back()->with('error', '无权限操作');
        }

        if ($lostPet->photo_path) {
            Storage::disk('public')->delete($lostPet->photo_path);
        }

        $lostPet->delete();

        return redirect()->route('lost-pets.index')->with('success', '删除成功！');
    }

    public function storeClue(PetClueRequest $request, LostPet $lostPet)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        if ($lostPet->status !== 'lost') {
            return back()->with('error', '该宠物已找到或已关闭，无法添加线索');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('pet_clues', 'public');
        }

        PetClue::create([
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

        return back()->with('success', '线索提交成功，感谢您的帮助！');
    }

    public function markFound(Request $request, LostPet $lostPet)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        if ($lostPet->user_id !== $user->id && !$user->isAdmin()) {
            return back()->with('error', '无权限操作');
        }

        $request->validate([
            'thank_you_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $lostPet->markAsFound($request->thank_you_note);

        return back()->with('success', '已标记为找到，感谢大家的帮助！');
    }

    public function close(LostPet $lostPet)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        if (!$user->isAdmin()) {
            return back()->with('error', '无权限操作');
        }

        $lostPet->close();

        return back()->with('success', '帖子已关闭');
    }

    public function verifyClue(PetClue $clue)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        if ($clue->lostPet->user_id !== $user->id && !$user->isAdmin()) {
            return back()->with('error', '无权限操作');
        }

        $clue->verify();

        return back()->with('success', '线索已核实');
    }

    public function destroyClue(PetClue $clue)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        if (!$clue->canEdit($user)) {
            return back()->with('error', '无权限操作');
        }

        if ($clue->photo_path) {
            Storage::disk('public')->delete($clue->photo_path);
        }

        $clue->lostPet->decrement('clue_count');
        $clue->delete();

        return back()->with('success', '删除成功');
    }
}
