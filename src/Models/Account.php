<?php

namespace Opscale\NovaServiceDesk\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasUlids, SoftDeletes, ValidatorTrait;

    protected $table = 'service_desk_accounts';

    protected $fillable = [
        'customer_type',
        'customer_id',
        'profile',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function customer(): MorphTo
    {
        return $this->morphTo();
    }

    public function insights(): HasMany
    {
        return $this->hasMany(Insight::class);
    }

    public function policies(): BelongsToMany
    {
        return $this->belongsToMany(SLAPolicy::class, 'service_desk_account_sla_policy', 'account_id', 'sla_policy_id')->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'service_desk_account_category', 'account_id', 'category_id')->withTimestamps();
    }
}
