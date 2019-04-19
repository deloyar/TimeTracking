<?php

namespace App\Http\Controllers;
use Validator;
use App\Effort;
use App\Task;
use JWTAuth;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth',
            ['only' => ['store', 'update', 'destroy'] ]
        );
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Task::all();
        foreach ($tasks as $task) {
            $tasks->view_task = [
                'href' => 'api/v1/task/' . $task->id,
                'method' => 'GET'
            ];
        }

        $response = [
            'msg' => 'List of all Task',
            'task' => $tasks
        ];
        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'state_id' => 'required',
            'effort' => 'required',
            'remain_effort' => 'required',
            'completed_effort' => 'required',
            'start_date' => 'required',
            'close_date' => 'required'
            /*
             * 'start_date' => 'required|date_format:YmdHie',
            'close_date' => 'required|date_format:YmdHie',
            */
        ]);

        if($validator->fails())
            return response()->json($validator->errors(), 404);

        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }


        $projectid = $request->input('project_id');
        $title = $request->input('title');
        $description = $request->input('description');
        $stateid = $request->input('state_id');
        $effort = $request->input('effort');
        $remaineffort = $request->input('remain_effort');
        $completedeffort = $request->input('completed_effort');
        $sdate = $request->input('start_date');
        $cdate = $request->input('close_date');
        $user_id = $user->id;

        $task = new Task([

            'project_id' => $projectid,
            'title' => $title,
            'description' => $description,
            'state_id' => $stateid,
            'user_id' => $user_id,
            'effort' => $effort,
            'remain_effort' => $remaineffort,
            'completed_effort' => $completedeffort,
            'start_date' => $sdate,
            'end_date' => $cdate
        ]);
        if ($task->save()) {
            $task->view_task = [
                'href' => 'api/v1/task/' . $task->id,
                'method' => 'GET'
            ];
            $message = [
                'msg' => 'Task created',
                'task' => $task
            ];
            return response()->json($message, 201);
        }

        $response = [
            'msg' => 'Error during creation'
        ];

        return response()->json($response, 404);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Task::where('id', $id)->firstOrFail();
        $task->view_task = [
            'href' => 'api/v1/task',
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'task information',
            'task' => $task
        ];
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'=> 'required',
            'description'=> 'required',
            'state_id' => 'required',
            'effort' => 'required',
            'remain_effort' => 'required',
            'completed_effort' => 'required',

        ]);

        if($validator->fails())
            return response()->json($validator->errors(), 404);

        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }


        $title = $request->input('title');
        $description = $request->input('description');
        $stateid = $request->input('state_id');
        $effort = $request->input('effort');
        $remaineffort = $request->input('remain_effort');
        $completedeffort = $request->input('completed_effort');

        // null check requred
        $task = Task::findOrFail($id);

        if ($task->user_id!=$user->id && $user->role_id!=1) {
            return response()->json(['msg' => 'This task is created by another user'], 401);
        };

        $exffort = $task->completed_effort;
        if($completedeffort<$exffort){
            return response()->json(['msg' => 'User effort is invalid'], 401);
        }

        $task->title = $title;
        $task->description = $description;
        $task->state_id = $stateid;
        $task->effort = $effort;
        $task->remain_effort = $remaineffort;
        $task->completed_effort = $completedeffort;
        if($task->update()){
            if($exffort<$completedeffort){
                $eff = new Effort([

                    'user_id' => $task->user_id,
                    'task_id' =>$task->id,
                    'effort_hours'=> $completedeffort - $exffort,
                    'date' =>date("Y-m-d")

                ]);
                $eff->save();
                //Rollback if return false
            }

        }else{
            return response()->json(['msg' => 'Error during updating'], 404);
        }

        $task->view_task = [
            'href' => 'api/v1/task/' . $task->id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Task updated',
            'project' => $task
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }
        //Only Admin can delete
        if ($user->role_id!=1) {
            return response()->json(['msg' => 'Your do not have permission to delete'], 401);
        };

        if (!$task->delete()) {
            return response()->json(['msg' => 'deletion failed'], 404);
        }

        $response = [
            'msg' => 'Task deleted',
            'create' => [
                'href' => 'api/v1/task',
                'method' => 'POST',
                'params' => 'title, description, time'
            ]
        ];

        return response()->json($response, 200);
    }
}
