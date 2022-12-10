<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\SubTask;
use App\Http\Resources\SubTaskListResource;

class TaskDetailsResource extends JsonResource
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
        $SubTaskList = SubTask::where('task_id', $this->id)->get();
        return [
            'id'            => isset($this->id) && !empty($this->id) ? (int)$this->id : "",
            'title'         => isset($this->title) && !empty($this->title) ? (String)$this->title : "",
            'due_date'      => isset($this->due_date) && !empty($this->due_date) ? (String)$this->due_date : "",
            'status'        => $this->status == 0 ? (String)"Pending" : "Completed",
            'sub_task_list' =>  (count($SubTaskList) > 0) ? SubTaskListResource::collection($SubTaskList) : [],
        ];
    }
}
