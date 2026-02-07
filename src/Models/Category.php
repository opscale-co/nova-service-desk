<?php

namespace Opscale\NovaServiceDesk\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Opscale\NovaServiceDesk\Models\Repositories\CategoryRepository;

class Category extends Model
{
    use CategoryRepository, HasUlids, ValidatorTrait;

    /**
     * @var array<string, array<int, string>>
     */
    public array $validationRules = [
        'description' => ['nullable', 'max:512'],
        'name' => ['required', 'max:256'],
        'key' => ['required', 'max:25'],
        'impact_options' => ['nullable', 'string'],
        'urgency_options' => ['nullable', 'string'],
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_desk_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'key',
        'description',
        'impact_options',
        'urgency_options',
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
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
