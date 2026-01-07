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
        'id_item',
        'id_location',
        'type',
        'problem',
        'cause',
        'curative',
        'attachment',
        'status',
        'id_user',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project', 'id_project');
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }

    public function kanban()
    {
        return $this->belongsTo(Kanban::class, 'id_kanban', 'id_kanban');
    }

    public function itemRelation()
    {
        return $this->belongsTo(Item::class, 'item', 'id_item');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'id_location', 'id_location');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function attachments()
    {
        return $this->hasMany(ProblemAttachment::class, 'problem_id', 'id_problem');
    }
}
