<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder search(Builder $query, string|null $search): void
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'frequency'
    ];

    /**
     * Search for products by name.
     *
     * @param Builder $query
     * @param string|null $search
     * @return void
     */
    public function scopeSearch(Builder $query, string|null $search): void
    {
        if (!is_null($search)) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
    }
}
