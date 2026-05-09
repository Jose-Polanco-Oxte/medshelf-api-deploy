<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $public_id
 * @property int $profile_id
 * @property int $item_id
 * @property string $status
 * @property int $frequency_value
 * @property string $frequency_unit
 * @property float $dose_quantity
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\ProfileModel $profile
 * @property-read \App\Models\ItemModel $item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConsumptionModel> $consumptions
 */
#[Table('treatments')]
#[Fillable(['public_id', 'profile_id', 'item_id', 'status', 'frequency_value', 'frequency_unit', 'dose_quantity', 'start_date', 'end_date'])]
class TreatmentModel extends Model
{
    use HasFactory;

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ProfileModel::class, 'profile_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemModel::class, 'item_id');
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(ConsumptionModel::class, 'treatment_id');
    }

    protected function casts(): array
    {
        return [
            'frequency_value' => 'integer',
            'dose_quantity'   => 'float',
            'start_date'      => 'datetime',
            'end_date'        => 'datetime',
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
            'deleted_at'      => 'datetime',
        ];
    }
}
