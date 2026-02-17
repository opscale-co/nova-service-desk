<?php

namespace Opscale\NovaServiceDesk\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Nova\Actions\Actionable;
use Opscale\NovaDynamicResources\Models\Concerns\UsesTemplate;
use Opscale\NovaServiceDesk\Models\Repositories\RequestRepository;

class Request extends Model
{
    use Actionable, HasUlids, RequestRepository, UsesTemplate, ValidatorTrait;

    protected $table = 'service_desk_requests';

    protected $fillable = [
        'account_id',
        'category_id',
        'subcategory_id',
        'assigned',
        'tracking_code',
        'data',
    ];

    protected $casts = [
        'assigned' => 'boolean',
        'data' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function task(): HasOne
    {
        return $this->hasOne(Task::class, 'request_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
