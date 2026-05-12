<?php

namespace App\Models;

use Database\Factories\PlaceModelFactory;
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
 * @property int $house_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\HouseModel $house
 * @property-read Collection<int, \App\Models\StorageModel> $storages
 * @property-read int|null $storages_count
 * @method static \Database\Factories\PlaceModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|PlaceModel newModelQuery()
 * @method static Builder<static>|PlaceModel newQuery()
 * @method static Builder<static>|PlaceModel query()
 * @method static Builder<static>|PlaceModel whereCreatedAt($value)
 * @method static Builder<static>|PlaceModel whereDeletedAt($value)
 * @method static Builder<static>|PlaceModel whereHouseId($value)
 * @method static Builder<static>|PlaceModel whereId($value)
 * @method static Builder<static>|PlaceModel whereName($value)
 * @method static Builder<static>|PlaceModel wherePublicId($value)
 * @method static Builder<static>|PlaceModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PlaceModel extends Model
{
    /** @uses HasFactory<PlaceModelFactory> */
    use HasFactory;

    protected $table = 'places';

    protected $fillable = ['public_id', 'house_id', 'name'];

    public function house(): BelongsTo
    {
        return $this->belongsTo(HouseModel::class, 'house_id');
    }

    public function storages(): HasMany
    {
        return $this->hasMany(StorageModel::class, 'place_id');
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
