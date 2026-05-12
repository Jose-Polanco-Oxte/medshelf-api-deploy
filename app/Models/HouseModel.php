<?php

namespace App\Models;

use Database\Factories\HouseModelFactory;
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
 * @property int $owner_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\User $owner
 * @property-read Collection<int, \App\Models\PlaceModel> $places
 * @property-read int|null $places_count
 * @method static \Database\Factories\HouseModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|HouseModel newModelQuery()
 * @method static Builder<static>|HouseModel newQuery()
 * @method static Builder<static>|HouseModel query()
 * @method static Builder<static>|HouseModel whereCreatedAt($value)
 * @method static Builder<static>|HouseModel whereDeletedAt($value)
 * @method static Builder<static>|HouseModel whereId($value)
 * @method static Builder<static>|HouseModel whereName($value)
 * @method static Builder<static>|HouseModel whereOwnerId($value)
 * @method static Builder<static>|HouseModel wherePublicId($value)
 * @method static Builder<static>|HouseModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class HouseModel extends Model
{
    /** @uses HasFactory<HouseModelFactory> */
    use HasFactory;

    protected $table = 'houses';

    protected $fillable = ['public_id', 'owner_id', 'name'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function places(): HasMany
    {
        return $this->hasMany(PlaceModel::class, 'house_id');
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
