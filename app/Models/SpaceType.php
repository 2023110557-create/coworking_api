<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpaceType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }
}
