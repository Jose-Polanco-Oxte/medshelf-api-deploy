<?php

namespace App\Models;

use Database\Factories\ActiveIngredientModelFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static ActiveIngredientModelFactory factory($count = null, $state = [])
 * @method static Builder<static>|ActiveIngredientModel newModelQuery()
 * @method static Builder<static>|ActiveIngredientModel newQuery()
 * @method static Builder<static>|ActiveIngredientModel query()
 * @method static Builder<static>|ActiveIngredientModel whereCreatedAt($value)
 * @method static Builder<static>|ActiveIngredientModel whereDeletedAt($value)
 * @method static Builder<static>|ActiveIngredientModel whereId($value)
 * @method static Builder<static>|ActiveIngredientModel whereName($value)
 * @method static Builder<static>|ActiveIngredientModel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ActiveIngredientModel extends Model
{
    /** @use HasFactory<ActiveIngredientModelFactory> */
    use HasFactory;
    
    use SoftDeletes;

    protected $table = 'active_ingredients';

    protected $fillable = ['name'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
