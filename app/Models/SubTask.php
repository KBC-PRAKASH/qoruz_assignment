<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class SubTask extends Model
{
    const STATUS_COMPLETED = 1;
    const STATUS_PENDING = 0;

    use HasFactory, SoftDeletes;

    protected $table = "sub_task";

    protected $fillable = [
        'task_id', 'title', 'due_date', 'description', 'ip_address', 'user_agent', 'status', 'added_by', 'updated_by'
    ];

    public static $rules = [
        'task_id'       => 'required|numeric|exists:task,id',
        'title'         => 'required',
        'due_date'      => 'required|date_format:Y-m-d',
    ];

    public static $updateRules = [
        'title'         => 'required',
        'due_date'      => 'required|date_format:Y-m-d',
    ];

    // Relationships here
    
}
