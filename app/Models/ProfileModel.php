<?php

namespace App\Models;

use Database\Factories\ProfileModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $public_id
 * @property int $user_id
 * @property string $name
 * @property string|null $relationship
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\ProfileModelFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel whereRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileModel whereUserId($value)
 * @mixin \Eloquent
 */
class ProfileModel extends Model
{
    /** @use HasFactory<ProfileModelFactory> */
    use HasFactory;

    protected $table = 'profiles';

    protected $fillable = ['public_id', 'user_id', 'name', 'relationship'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
