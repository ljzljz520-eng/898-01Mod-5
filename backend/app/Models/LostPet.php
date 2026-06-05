<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LostPet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'pet_name',
        'pet_type',
        'breed',
        'color',
        'collar_features',
        'description',
        'photo_path',
        'last_seen_lat',
        'last_seen_lng',
        'last_seen_address',
        'last_seen_at',
        'contact_phone',
        'contact_name',
        'thank_you_note',
        'status',
        'view_count',
        'clue_count',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'view_count' => 'integer',
        'clue_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clues()
    {
        return $this->hasMany(PetClue::class);
    }

    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    public function scopeFound($query)
    {
        return $query->where('status', 'found');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeNearby($query, $lat, $lng, $radius = 10)
    {
        $haversine = "(6371 * acos(cos(radians({$lat})) * cos(radians(last_seen_lat)) * cos(radians(last_seen_lng) - radians({$lng})) + sin(radians({$lat})) * sin(radians(last_seen_lat))))";
        return $query->select('*')
            ->selectRaw("{$haversine} AS distance")
            ->whereRaw("{$haversine} < ?", [$radius])
            ->orderBy('distance', 'asc');
    }

    public function markAsFound($thankYouNote = null)
    {
        $this->update([
            'status' => 'found',
            'thank_you_note' => $thankYouNote ?? $this->thank_you_note,
        ]);
    }

    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    public function isOwner($user)
    {
        return $user && $this->user_id === $user->id;
    }

    public function canViewPrivateClues($user)
    {
        if (!$user) return false;
        return $this->isOwner($user) || $user->isAdmin();
    }
}
