<?php

namespace Opscale\NovaServiceDesk\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NovaServiceDesk\Models\Enums\SLAPolicyStatus;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;

class SLAPolicy extends Model
{
    use HasUlids, SoftDeletes, ValidatorTrait;

    protected $table = 'service_desk_sla_policies';

    protected $fillable = [
        'name',
        'description',
        'priority',
        'status',
        'max_contact_time',
        'max_resolution_time',
        'update_frequency',
        'supported_channels',
        'service_timezone',
        'service_time',
        'service_exceptions',
    ];

    protected $casts = [
        'priority' => SLAPriority::class,
        'status' => SLAPolicyStatus::class,
        'supported_channels' => 'array',
        'service_time' => 'array',
        'service_exceptions' => 'array',
    ];

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'service_desk_account_sla_policy', 'sla_policy_id', 'account_id')->withTimestamps();
    }
}
