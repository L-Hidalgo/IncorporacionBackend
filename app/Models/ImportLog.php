<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $table = 'import_dde_logs_acciones_usuarios';

    protected $fillable = [
        'id',
        'usuario_id',
    ];

    public function usuario() {
        return $this->belongsTo(User::class,'usuario_id','id');
    }
}
