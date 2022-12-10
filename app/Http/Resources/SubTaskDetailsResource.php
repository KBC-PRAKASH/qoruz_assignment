<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Task;

class SubTaskDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        $taskName = Task::find($this->task_id)->value('title');
        return [
            'id'            => isset($this->id) && !empty($this->id) ? (int)$this->id : "",
            'task_title'    => isset($taskName) && !empty($taskName) ? (String)$taskName : "",
            'title'         => isset($this->title) && !empty($this->title) ? (String)$this->title : "",
            'due_date'      => isset($this->due_date) && !empty($this->due_date) ? (String)$this->due_date : "",
            'description'   => isset($this->description) && !empty($this->description) ? (String)$this->description : "",
            'status'        => $this->status == 0 ? (String)"Pending" : "Completed", 
        ];
    }
}
