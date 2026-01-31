<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturingProblemAttachment extends Model
{
    use HasFactory;

    protected $table = 'manufacturing_problem_attachments';

    protected $fillable = ['problem_id', 'file_path'];

    public function problem()
    {
        return $this->belongsTo(ManufacturingProblem::class, 'problem_id', 'id_problem');
    }
}
