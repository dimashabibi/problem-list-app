<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curative extends Model
{
    use HasFactory;

    protected $table = 'curatives';
    protected $primaryKey = 'id_curative';

    protected $fillable = [
        'id_problem',
        'id_pic',
        'curative',
        'hour',
    ];

    public function problem()
    {
        return $this->belongsTo(Problem::class, 'id_problem', 'id_problem');
    }

    public function pic()
    {
        return $this->belongsTo(Location::class, 'id_pic', 'id_location');
    }
}
