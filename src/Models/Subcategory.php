<?php

namespace Opscale\NovaServiceDesk\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcategory extends Model
{
    use HasUlids, ValidatorTrait;

    /**
     * @var array<string, array<int, string>>
     */
    public array $validationRules = [
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
