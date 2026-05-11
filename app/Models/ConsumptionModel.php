<?php

namespace App\Models;

use Database\Factories\ConsumptionModelFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $public_id
 * @property int $item_id
 * @property int|null $treatment_id
 * @property float $amount
 * @property Carbon $consumed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\ItemModel $item
 * @property-read \App\Models\TreatmentModel|null $treatment
 * @method static \Database\Factories\ConsumptionModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|ConsumptionModel newModelQuery()
 * @method static Builder<static>|ConsumptionModel newQuery()
 * @method static Builder<static>|ConsumptionModel query()
 * @method static Builder<static>|ConsumptionModel whereAmount($value)
 * @method static Builder<static>|ConsumptionModel whereConsumedAt($value)
 * @method static Builder<static>|ConsumptionModel whereCreatedAt($value)
 * @method static Builder<static>|ConsumptionModel whereDeletedAt($value)
 * @method static Builder<static>|ConsumptionModel whereId($value)
 * @method static Builder<static>|ConsumptionModel whereItemId($value)
 * @method static Builder<static>|ConsumptionModel wherePublicId($value)
 * @method static Builder<static>|ConsumptionModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
#[Table('consumptions')]
#[Fillable(['public_id', 'item_id', 'treatment_id', 'amount', 'consumed_at'])]
class ConsumptionModel extends Model
{
    /** @uses HasFactory<ConsumptionModelFactory> */
    use HasFactory;

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemModel::class, 'item_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(TreatmentModel::class, 'treatment_id');
    }

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'consumed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
