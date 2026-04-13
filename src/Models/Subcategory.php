<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Opscale\Validations\Validatable;

class Subcategory extends Model
{
    use HasUlids, Validatable;

    /**
     * @var array<string, array<int, string>>
     */
    public static array $validationRules = [
        'description' => ['nullable', 'max:512'],
        'name' => ['required', 'max:256'],
        'key' => ['required', 'max:25'],
        'impact' => ['nullable', 'string'],
        'urgency' => ['nullable', 'string'],
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_desk_subcategories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'key',
        'description',
        'impact',
        'urgency',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the requests for the subcategory.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }
}
