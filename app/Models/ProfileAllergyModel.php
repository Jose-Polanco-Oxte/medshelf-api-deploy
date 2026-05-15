<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $profile_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read ProfileModel $profile
 * @method static Builder<static>|ProfileAllergyModel newModelQuery()
 * @method static Builder<static>|ProfileAllergyModel newQuery()
 * @method static Builder<static>|ProfileAllergyModel query()
 * @method static Builder<static>|ProfileAllergyModel whereCreatedAt($value)
 * @method static Builder<static>|ProfileAllergyModel whereDeletedAt($value)
 * @method static Builder<static>|ProfileAllergyModel whereId($value)
 * @method static Builder<static>|ProfileAllergyModel whereName($value)
 * @method static Builder<static>|ProfileAllergyModel whereProfileId($value)
 * @method static Builder<static>|ProfileAllergyModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ProfileAllergyModel extends Model
{

    protected $table = 'profile_allergies';

    protected $fillable = ['profile_id', 'name'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ProfileModel::class, 'profile_id');
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

