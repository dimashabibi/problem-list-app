<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preventive extends Model
{
    use HasFactory;

    protected $table = 'preventives';
    protected $primaryKey = 'id_preventive';

    protected $fillable = [
        'id_problem',
        'preventive',
    ];

    public function problem()
    {
        return $this->belongsTo(Problem::class, 'id_problem', 'id_problem');
    }
}
