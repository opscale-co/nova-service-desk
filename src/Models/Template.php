<?php

namespace Opscale\NovaServiceDesk\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Opscale\NovaDynamicResources\Models\Template as BaseTemplate;

class Template extends BaseTemplate
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynamic_resources_templates';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'name',
        'identifier',
        'description',
    ];

    /**
     * Get the categories for the template.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'service_desk_category_template', 'template_id', 'category_id')
            ->using(CategoryTemplate::class)
            ->withTimestamps();
    }
}
