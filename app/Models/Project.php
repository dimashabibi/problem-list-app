<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $primaryKey = 'id_project';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'project_name',
        'description',
    ];
}
