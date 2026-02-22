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
        'id_machine',
        'type',
        'type_saibo',
        'problem',
        'cause',
        'status',
        'id_user',
        'group_code',
        'group_code_norm',
        'classification',
        'stage',
        'id_seksi_in_charge',
        'classification_problem',
        'dispatched_at',
        'closed_at',
        'target',
    ];

    const UPDATED_AT = null;

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

    public function machine()
    {
        return $this->belongsTo(Machine::class, 'id_machine', 'id_machine');
    }
    
    public function seksiInCharge()
    {
        return $this->belongsTo(Location::class, 'id_seksi_in_charge', 'id_location');
    }

    public function curatives()
    {
        return $this->hasMany(Curative::class, 'id_problem', 'id_problem');
    }

    public function preventives()
    {
        return $this->hasMany(Preventive::class, 'id_problem', 'id_problem');
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
