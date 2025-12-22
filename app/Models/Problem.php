<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    use HasFactory;

    protected $table = 'problems';
    protected $primaryKey = 'id_problem';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_project',
        'id_kanban',
        'item',
        'id_location',
        'type',
        'problem',
        'cause',
        'curative',
        'attacment',
        'status',
        'id_user',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project', 'id_project');
    }

    public function kanban()
    {
        return $this->belongsTo(Kanban::class, 'id_kanban', 'id_kanban');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'id_location', 'id_location');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
