<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Performance extends Model
{
    public $timestamps = false;

    protected $table = 'performance';

    protected $fillable = ['id_grupo', 'eDesempeño', 'bDesempeño', 'arDesempeño', 'comentarios'];

    protected $casts = ['id_grupo' => 'integer', 'eDesempeño' => 'integer', 'bDesempeño' => 'integer', 'arDesempeño' => 'integer'];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoMaster::class, 'id_grupo');
    }
}
