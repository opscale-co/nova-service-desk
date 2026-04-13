<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Opscale\NovaServiceDesk\Models\Repositories\ResolutionRepository;
use Opscale\Validations\Validatable;

class Resolution extends Model
{
    use HasUlids, ResolutionRepository, Validatable;

    protected $table = 'service_desk_resolutions';

    protected $fillable = [
        'subcategory_id',
        'documentation_url',
        'notes',
        'author',
        'last_modified',
    ];

    protected $casts = [
        'last_modified' => 'date',
    ];

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }
}
