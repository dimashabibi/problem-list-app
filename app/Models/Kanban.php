<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kanban extends Model
{
    use HasFactory;

    protected $table = 'kanbans';
    protected $primaryKey = 'id_kanban';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'project_id',
        'kanban_name',
        'part_name',
        'part_number',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id_project');
    }
}
