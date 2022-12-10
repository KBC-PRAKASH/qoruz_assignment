<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Task extends Model
{
    const STATUS_COMPLETED = 1;
    const STATUS_PENDING = 0;

    use HasFactory, SoftDeletes;

    protected $table = "task";

    protected $fillable = [
        'title', 'due_date', 'ip_address', 'user_agent', 'status', 'added_by', 'updated_by'
    ];

    public static $rules = [
        'title'         => 'required',
        'due_date'      => 'required|date_format:Y-m-d',
    ];

    // Relationships here
    public function sub_task() {
        return $this->hasMany(SubTask::class, 'task_id');
    }
}
