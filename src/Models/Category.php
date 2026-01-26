<?php

namespace Opscale\NovaServiceDesk\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Opscale\NovaCatalogs\Models\Catalog;

class Category extends Catalog
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'catalogs';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'impact_options',
        'urgency_options',
    ];

    /**
     * Alias for items relationship.
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class, 'catalog_id');
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'service_desk_account_category', 'category_id', 'account_id')->withTimestamps();
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(Template::class, 'service_desk_category_template', 'category_id', 'template_id')
            ->using(CategoryTemplate::class)
            ->withTimestamps();
    }
}
