<?php

namespace Opscale\NovaServiceDesk\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Opscale\NovaCatalogs\Models\CatalogItem;

class Subcategory extends CatalogItem
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'catalog_items';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'impact',
        'urgency',
    ];

    /**
     * Alias for catalog relationship.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'catalog_id');
    }

    /**
     * Get the requests for the subcategory.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'data->subcategory_id');
    }
}
