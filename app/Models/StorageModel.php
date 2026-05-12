<?php

namespace App\Models;

use Database\Factories\StorageModelFactory;
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
 * @property int $place_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \App\Models\ItemModel> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\PlaceModel $place
 * @method static \Database\Factories\StorageModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|StorageModel newModelQuery()
 * @method static Builder<static>|StorageModel newQuery()
 * @method static Builder<static>|StorageModel query()
 * @method static Builder<static>|StorageModel whereCreatedAt($value)
 * @method static Builder<static>|StorageModel whereDeletedAt($value)
 * @method static Builder<static>|StorageModel whereId($value)
 * @method static Builder<static>|StorageModel whereName($value)
 * @method static Builder<static>|StorageModel wherePlaceId($value)
 * @method static Builder<static>|StorageModel wherePublicId($value)
 * @method static Builder<static>|StorageModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class StorageModel extends Model
{
    /** @uses HasFactory<StorageModelFactory> */
    use HasFactory;

    protected $table = 'storages';

    protected $fillable = ['public_id', 'place_id', 'name'];

    public function place(): BelongsTo
    {
        return $this->belongsTo(PlaceModel::class, 'place_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemModel::class, 'storage_id');
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
