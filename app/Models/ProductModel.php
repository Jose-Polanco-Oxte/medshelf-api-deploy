<?php

namespace App\Models;

use Database\Factories\ProductModelFactory;
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
 * @property string $name
 * @property float|null $net_content_value
 * @property string|null $net_content_unit
 * @property int|null $total_quantity
 * @property int $pharmaceutical_form_id
 * @property float $composition_reference_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \App\Models\ProductCompoundModel> $activeCompounds
 * @property-read int|null $active_compounds_count
 * @property-read \App\Models\PharmaceuticalFormModel $pharmaceuticalForm
 * @method static \Database\Factories\ProductModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|ProductModel newModelQuery()
 * @method static Builder<static>|ProductModel newQuery()
 * @method static Builder<static>|ProductModel query()
 * @method static Builder<static>|ProductModel whereCompositionReferenceAmount($value)
 * @method static Builder<static>|ProductModel whereCreatedAt($value)
 * @method static Builder<static>|ProductModel whereDeletedAt($value)
 * @method static Builder<static>|ProductModel whereId($value)
 * @method static Builder<static>|ProductModel whereName($value)
 * @method static Builder<static>|ProductModel whereNetContentUnit($value)
 * @method static Builder<static>|ProductModel whereNetContentValue($value)
 * @method static Builder<static>|ProductModel wherePharmaceuticalFormId($value)
 * @method static Builder<static>|ProductModel wherePublicId($value)
 * @method static Builder<static>|ProductModel whereTotalQuantity($value)
 * @method static Builder<static>|ProductModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ProductModel extends Model
{
    /** @use HasFactory<ProductModelFactory> */
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'public_id',
        'name',
        'net_content_value',
        'net_content_unit',
        'total_quantity',
        'pharmaceutical_form_id',
        'composition_reference_amount',
    ];

    public function activeCompounds(): HasMany
    {
        return $this->hasMany(ProductCompoundModel::class, 'product_id');
    }

    public function pharmaceuticalForm(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalFormModel::class, 'pharmaceutical_form_id');
    }

    protected function casts(): array
    {
        return [
            'net_content_value' => 'float',
            'total_quantity' => 'integer',
            'composition_reference_amount' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
