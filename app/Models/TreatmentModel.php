<?php

namespace App\Models;

use Database\Factories\TreatmentModelFactory;
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
 * @property int $profile_id
 * @property int $item_id
 * @property string $status
 * @property float $dose
 * @property int $frequency_hours
 * @property Carbon $start_date
 * @property int $days
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, ConsumptionModel> $consumptions
 * @property-read int|null $consumptions_count
 * @property-read ItemModel $item
 * @property-read ProfileModel $profile
 * @method static TreatmentModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|TreatmentModel newModelQuery()
 * @method static Builder<static>|TreatmentModel newQuery()
 * @method static Builder<static>|TreatmentModel query()
 * @method static Builder<static>|TreatmentModel whereCreatedAt($value)
 * @method static Builder<static>|TreatmentModel whereDeletedAt($value)
 * @method static Builder<static>|TreatmentModel whereDose($value)
 * @method static Builder<static>|TreatmentModel whereEndDate($value)
 * @method static Builder<static>|TreatmentModel whereFrequencyHours($value)
 * @method static Builder<static>|TreatmentModel whereId($value)
 * @method static Builder<static>|TreatmentModel whereItemId($value)
 * @method static Builder<static>|TreatmentModel whereProfileId($value)
 * @method static Builder<static>|TreatmentModel wherePublicId($value)
 * @method static Builder<static>|TreatmentModel whereStartDate($value)
 * @method static Builder<static>|TreatmentModel whereStatus($value)
 * @method static Builder<static>|TreatmentModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TreatmentModel extends Model
{
    use HasFactory;

    protected $table = 'treatments';

    protected $fillable = ['public_id', 'profile_id', 'item_id', 'status', 'dose', 'frequency_hours', 'start_date', 'days'];

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
            'dose' => 'float',
            'frequency_hours' => 'integer',
            'start_date' => 'datetime',
            'days' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
