<?php

namespace Opscale\NovaServiceDesk\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryTemplate extends Pivot
{
    use HasUlids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_desk_category_template';

    /**
     * Get the category that belongs to this pivot.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the template that belongs to this pivot.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
