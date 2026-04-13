<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Workbench example model representing a serviceable business unit.
 *
 * Departments are the entities that REQUIRE service in the workbench
 * domain — their assets, processes and shared mailboxes can have
 * tickets placed on them. The mapping between this model and the
 * service desk contracts is performed by {@see \Workbench\App\Resolvers\ServiceResolver}
 * so that the model itself stays free of cross-cutting concerns.
 */
class Department extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'manager_id',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
