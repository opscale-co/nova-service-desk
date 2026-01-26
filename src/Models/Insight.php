<?php

namespace Opscale\NovaServiceDesk\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaServiceDesk\Models\Enums\InsightScope;
use Opscale\NovaServiceDesk\Models\Repositories\InsightRepository;

class Insight extends Model
{
    use HasUlids, InsightRepository, SoftDeletes, ValidatorTrait;

    protected $table = 'service_desk_insights';

    protected $fillable = [
        'account_id',
        'author',
        'scope',
        'title',
        'details',
        'attachment_url',
    ];

    protected $casts = [
        'scope' => InsightScope::class,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
