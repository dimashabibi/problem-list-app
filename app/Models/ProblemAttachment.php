<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['problem_id', 'file_path'];

    public function problem()
    {
        return $this->belongsTo(Problem::class, 'problem_id', 'id_problem');
    }
}
