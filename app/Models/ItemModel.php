<?php

namespace App\Models;

use Database\Factories\ItemModelFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
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
 * @property int $product_id
 * @property int $storage_id
 * @property float $total_content
 * @property Carbon $expiration_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, ConsumptionModel> $consumptions
 * @property-read int|null $consumptions_count
 * @property-read ProductModel $product
 * @property-read StorageModel $storage
 * @method static ItemModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|ItemModel newModelQuery()
 * @method static Builder<static>|ItemModel newQuery()
 * @method static Builder<static>|ItemModel query()
 * @method static Builder<static>|ItemModel whereCreatedAt($value)
 * @method static Builder<static>|ItemModel whereDeletedAt($value)
 * @method static Builder<static>|ItemModel whereExpirationDate($value)
 * @method static Builder<static>|ItemModel whereId($value)
 * @method static Builder<static>|ItemModel whereProductId($value)
 * @method static Builder<static>|ItemModel wherePublicId($value)
 * @method static Builder<static>|ItemModel whereStorageId($value)
 * @method static Builder<static>|ItemModel whereTotalContent($value)
 * @method static Builder<static>|ItemModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
#[Table('items')]
#[Fillable([
    'public_id',
    'product_id',
    'storage_id',
    'total_content',
    'expiration_date',
])]
class ItemModel extends Model
{
    /** @uses HasFactory<ItemModelFactory> */
    use HasFactory;

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(StorageModel::class, 'storage_id');
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(ConsumptionModel::class, 'item_id');
    }

    protected function casts(): array
    {
        return [
            'expiration_date' => 'datetime',
            'total_content' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
