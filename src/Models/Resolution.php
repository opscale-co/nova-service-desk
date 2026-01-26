<?php

namespace Opscale\NovaServiceDesk\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Opscale\NovaServiceDesk\Models\Repositories\ResolutionRepository;

class Resolution extends Model
{
    use HasUlids, ResolutionRepository, ValidatorTrait;

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
