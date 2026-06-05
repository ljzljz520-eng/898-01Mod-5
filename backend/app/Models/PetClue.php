<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PetClue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lost_pet_id',
        'user_id',
        'lat',
        'lng',
        'address',
        'seen_at',
        'description',
        'photo_path',
        'is_private',
        'is_verified',
    ];

    protected $casts = [
        'seen_at' => 'datetime',
        'is_private' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function lostPet()
    {
        return $this->belongsTo(LostPet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canView($user)
    {
        if (!$this->is_private) return true;
        if (!$user) return false;
        return $this->user_id === $user->id
            || $this->lostPet->user_id === $user->id
            || $user->isAdmin();
    }

    public function isOwner($user)
    {
        return $user && $this->user_id === $user->id;
    }

    public function canEdit($user)
    {
        if (!$user) return false;
        return $this->isOwner($user) || $user->isAdmin();
    }

    public function verify()
    {
        $this->update(['is_verified' => true]);
    }
}
