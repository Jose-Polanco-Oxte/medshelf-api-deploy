<?php

namespace App\Models;

use Database\Factories\ProfileModelFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property Carbon|null $birthdate
 * @property-read Collection<int, ProfileAllergyModel> $allergies
 * @property-read int|null $allergies_count
 * @property-read User $user
 * @method static ProfileModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|ProfileModel newModelQuery()
 * @method static Builder<static>|ProfileModel newQuery()
 * @method static Builder<static>|ProfileModel query()
 * @method static Builder<static>|ProfileModel whereBirthdate($value)
 * @method static Builder<static>|ProfileModel whereCreatedAt($value)
 * @method static Builder<static>|ProfileModel whereDeletedAt($value)
 * @method static Builder<static>|ProfileModel whereId($value)
 * @method static Builder<static>|ProfileModel whereName($value)
 * @method static Builder<static>|ProfileModel wherePublicId($value)
 * @method static Builder<static>|ProfileModel whereRelationship($value)
 * @method static Builder<static>|ProfileModel whereUpdatedAt($value)
 * @method static Builder<static>|ProfileModel whereUserId($value)
 * @mixin Eloquent
 */
class ProfileModel extends Model
{
    /** @use HasFactory<ProfileModelFactory> */
    use HasFactory;

    protected $table = 'profiles';

    protected $fillable = ['public_id', 'user_id', 'name', 'relationship', 'birthdate'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(ProfileAllergyModel::class, 'profile_id');
    }

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
