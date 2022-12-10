<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator, DB, DateTime;
use App\Models\Task as Model_Name;
use App\Models\SubTask;
use Carbon\Carbon;
use App\Http\Resources\TaskListResource;
use App\Http\Resources\TaskDetailsResource;

class TaskController extends Controller
{
    public function store(Request $request) {
        $results = ['status' => 0, 'message' => 'Some error occur'];
        try{
            $validation = \Validator::make($request->all(), Model_Name::$rules);
            if($validation->fails()){
                $results['message'] = $validation->errors();
            }else{
                $checkDuplicate = Model_Name::where('title', trim($request->title))->value('title');
                if($checkDuplicate) {
                    $results['message'] = "Task '".trim($request->title)."' is already created";
                }else{
                    if(Model_Name::create([
                        'title'         => trim($request->title),
                        'due_date'      => $request->due_date,
                        'ip_address'    => getClientIp(),
                        'user_agent'    => getBrowser(),
                        'status'        => Model_Name::STATUS_PENDING,
                        'added_by'      => 1,
                        'created_at'    => date('Y-m-d H:i:s'),
                        'updated_at'    => date('Y-m-d H:i:s'),
                    ])){
                        $results['status'] = 1;
                        $results['message'] = "Task created successfully";
                    }else{
                        $results['message'] = "Something went wron, try again after some time"; 
                    }
                }
                
            }
        }catch(\Exception $e){
            $results['message'] = $e->getMessage();
        }
        return $results;
    }

    public function filtering(Request $request) {
        $status = ['success' => 0, 'message' => 'Some error ocurred'];
        try{
            $page       = isset($request->page) && !empty($request->page) ? $request->page : 1;
            $pageSize   = isset($request->page_size) && !empty($request->page_size) ? $request->page_size : 10;
            
            $query = Model_Name::query();
            if(isset($request->title) && !empty($request->title)){
                $query->where('title', 'like', '%'.$request->title.'%');
            }
            if(isset($request->due_date) && !empty($request->due_date)){
                $formatedDate = date_format(date_create($request->due_date), "Y-m-d");
                $query->where('due_date', $formatedDate);
            }
            if(isset($request->status_key) && !empty($request->status_key)){
                if($request->status_key == "pending"){
                    $query->where('status', Model_Name::STATUS_PENDING);
                }else if($request->status_key == "completed") {
                    $query->where('status', Model_Name::STATUS_COMPLETED);
                }
            }
            if(isset($request->filter_key) && !empty($request->filter_key)){
                switch($request->filter_key) {
                    case "today":
                        $query->where('due_date', date("Y-m-d"));
                        break;
                    case "this_week": 
                        $now = Carbon::now();
                        $weekStartDate = $now->startOfWeek()->format('Y-m-d');
                        $weekEndDate = $now->endOfWeek()->format('Y-m-d');
                        $query->whereBetween('due_date',[$weekStartDate, $weekEndDate]);
                        break;
                    case "next_week": 
                        $nextWeekStartDate          = Carbon::parse('next monday')->toDateString();
                        $nextweekEndDate            = Carbon::parse('next sunday')->addDays(7)->toDateString();
                        $formatedNextWeekStartDate  = date_format(date_create($nextWeekStartDate), "Y-m-d");
                        $formatednextweekEndDate    = date_format(date_create($nextweekEndDate), "Y-m-d");
                        $query->whereBetween('due_date',[$formatedNextWeekStartDate, $formatednextweekEndDate]);
                        break;
                    case "Overdue":
                        if(isset($request->overdue_date) && !empty($request->overdue_date)){
                            $dateInputs = $request->overdue_date;
                            $explodeDates = explode("/", $dateInputs);
                            if(count($explodeDates) > 1){
                                $startDate  = date_format(date_create($explodeDates[0]), "Y-m-d");
                                $endDate    = date_format(date_create($explodeDates[1]), "Y-m-d");
                                $query->whereBetween('due_date',[$startDate, $endDate]);
                                break;
                            }else{
                                $results['message'] = "Please select two dates";
                                return $results;
                            }
                        }else{
                            break;
                        }
                    default: 
                        $results['message'] = "In-valid parameter";
                        return $results;
                        

                }
            }
            $query->orderBy('due_date','ASC');
            $results = $query->paginate($pageSize);
            if(count($results) > 0){
                $status['success'] = 1;
                $status['message'] = "Task found successfully";
                $status['records'] = TaskListResource::collection($results);

                $prev_page_with_url = $results->previousPageUrl();
                if($prev_page_with_url != null){
                    $prev_page = explode('=',$prev_page_with_url);
                    $pagination['previousPage'] = (Int)$prev_page[1];
                }else{
                    $pagination['previousPage'] = 0;
                }
                $next_page_with_url = $results->nextPageUrl(); 
                if($next_page_with_url != null){
                    $next_page = explode('=',$next_page_with_url);
                    $pagination['nextPage'] = (Int)$next_page[1];
                }else{
                    $pagination['nextPage'] = 0;
                }
                $pagination['total'] = $results->total();
                $pagination['currentPage'] = $results->currentPage();
                $pagination['pageSize'] = $results->perPage();
                $pagination['totalPages'] = $results->lastPage();
                $pagination['items'] = [];
                for($i=1; $i <= $results->lastPage(); $i++) {
                    $pagination['items'][] = $i;
                }
                $status['pagination'] = $pagination;
            }else{
                $status['message'] = "Record not found";
            }

        }catch(\Exception $e){
            $status['message'] = $e->getMessage();
        }
        return $status;
    }

    public function editRecord(Request $request) {
        $status = ['status' => 0, 'message' => 'Some error occur'];
        try{
            $id = $request->id;
            if(is_numeric($id)){
                $res = Model_Name::find($id);
                if(isset($res) && !empty($res)){
                    $status['status'] = 1;
                    $status['message'] = "Task found successfully";
                    $status['records'] = new TaskDetailsResource($res);
                }else{
                    $status['message'] = "Task not found";  
                }
            }else{
                $status['message'] = "In-valid paramter";
            }
        }catch(\Exception $e){
            $status['message'] = $e->getMessage();
        }
        return $status;
    }

    public function updateRecord(Request $request){
        $status = ['status' => 0, 'message' => 'Some error occur'];
        \DB::beginTransaction();
        try{
            $id = $request->id;
            if(is_numeric($id)){
                $validation = \Validator::make($request->all(), Model_Name::$rules);
                if($validation->fails()){
                    $status['message'] = $validation->errors();
                }else{
                    $checkDuplicate = Model_Name::where('id','!=', $id)->where('title', trim($request->title))->count();
                    if($checkDuplicate > 0){
                        $status['message'] = "Task '".trim($request->title)."' is already created";;
                    }else{
                        $updateRecord = Model_Name::where('id', $id)->update([
                            'title'         => trim($request->title),
                            'due_date'      => $request->due_date,
                            'ip_address'    => getClientIp(),
                            'user_agent'    => getBrowser(),
                            'status'        => (int)$request->status,
                            'updated_by'    => 1,
                            'updated_at'    => date('Y-m-d H:i:s'),
                        ]);

                        if($request->status == 1){
                            $updateSubTaskRecords = SubTask::where('task_id', $id)->update(['status' => $request->status, 'updated_by' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
                        }
                        \DB::commit();
                        $status['status'] = 1;
                        $status['message'] = "Task updated successfully";
                    }
                }
                
            }else{
                $status['message'] = "In-valid paramter";
            }
        }catch(\Exception $e){
            \DB::rollback();
            $status['message'] = $e->getMessage();
        }
        return $status;
    }

    public function delete(Request $request){
        $results = ['status' => 0, 'message' => 'Some error occur'];
        \DB::beginTransaction();
        try{
            if(isset($request->id) && !empty($request->id)){
                if(Model_Name::find($request->id)->delete()){
                    //  delete sub task
                    $deleteSubTask = SubTask::where('task_id', $request->id)->delete();
                    \DB::commit();

                    $results['status'] = 1;
                    $results['message'] = "Selected task is deleted successfully";
                }else{
                    $results['message'] = "Task not found";
                }
            }else{
                $results['message'] = "Required parameter is missing";
            }
            
        }catch(\Exception $e){
            \DB::rollback();
            $results['message'] = $e->getMessage();
        }
        return $results;
    }


    public function runScheduler(Request $request) {
        $results = ['status' => 0, 'message' => 'Some error occur'];
        \DB::beginTransaction();
        try{
            $result = Model_Name::onlyTrashed()->get();
            if(count($result) > 0){
                $totalCount = 0;
                foreach($result as $key => $value){
                    $fromDate = new DateTime($value->deleted_at);
                    $createdMonth = date_format(date_create($value->deleted_at), "m");
                    $createdYear = date_format(date_create($value->deleted_at), "m");
                    $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN,$createdMonth, $createdYear);
                    
                    $toDate = new DateTime(date('Y-m-d'));
                    $daydiff = date_diff($fromDate, $toDate);
                    if($daydiff->days > $totalDaysInMonth){
                        $totalCount++;
                        \DB::table('task')->where('id', $value->id)->delete();
                        \DB::table('sub_task')->where('task_id', $value->id)->delete();
                    }
                    \DB::commit();
                }
                if($totalCount > 0){
                    $results['status'] = 1;
                    $results['message'] = $totalCount. " record's deleted successfully.";
                }else{
                    $results['message'] = "Records not found";
                }

            }else{
                $results['message'] = "No records found";
            }
        }catch(\Exception $e){
            \DB::rollback();
            $results['message'] = $e->getMessage();
        }
        return $results;
    }
}


