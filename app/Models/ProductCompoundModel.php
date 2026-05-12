<?php

namespace App\Models;

use Database\Factories\ProductCompoundModelFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $active_ingredient_id
 * @property int $product_id
 * @property float $strength_value
 * @property string $strength_unit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\ActiveIngredientModel $activeIngredient
 * @property-read \App\Models\ProductModel $product
 * @method static \Database\Factories\ProductCompoundModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|ProductCompoundModel newModelQuery()
 * @method static Builder<static>|ProductCompoundModel newQuery()
 * @method static Builder<static>|ProductCompoundModel query()
 * @method static Builder<static>|ProductCompoundModel whereActiveIngredientId($value)
 * @method static Builder<static>|ProductCompoundModel whereCreatedAt($value)
 * @method static Builder<static>|ProductCompoundModel whereDeletedAt($value)
 * @method static Builder<static>|ProductCompoundModel whereId($value)
 * @method static Builder<static>|ProductCompoundModel whereProductId($value)
 * @method static Builder<static>|ProductCompoundModel whereStrengthUnit($value)
 * @method static Builder<static>|ProductCompoundModel whereStrengthValue($value)
 * @method static Builder<static>|ProductCompoundModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ProductCompoundModel extends Model
{
    /** @use HasFactory<ProductCompoundModelFactory> */
    use HasFactory;

    protected $table = 'product_compounds';

    protected $fillable = [
        'product_id',
        'active_ingredient_id',
        'strength_value',
        'strength_unit',
    ];

    public function activeIngredient(): BelongsTo
    {
        return $this->belongsTo(ActiveIngredientModel::class, 'active_ingredient_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    protected function casts(): array
    {
        return [
            'strength_value' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
