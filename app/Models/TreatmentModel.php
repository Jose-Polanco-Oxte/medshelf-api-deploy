<?php

namespace App\Models;

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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConsumptionModel> $consumptions
 * @property-read int|null $consumptions_count
 * @property-read \App\Models\ItemModel $item
 * @property-read \App\Models\ProfileModel $profile
 * @method static \Database\Factories\TreatmentModelFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereDoseQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereFrequencyUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereFrequencyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TreatmentModel extends Model
{
    use HasFactory;

    protected $table = 'treatments';

    protected $fillable = ['public_id', 'profile_id', 'item_id', 'status', 'frequency_value', 'frequency_unit', 'dose_quantity', 'start_date', 'end_date'];

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
