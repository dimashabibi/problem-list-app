<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'machines';
    protected $primaryKey = 'id_machine';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name_machine',
        'description',
    ];
}
