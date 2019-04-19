<?php

namespace App\Http\Controllers;

use App\Project;
use Carbon\Carbon;
use Validator;
use JWTAuth;
use Illuminate\Http\Request;

class ProjectController extends Controller
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
        $projects = Project::all();
        foreach ($projects as $project) {
            $project->view_project = [
                'href' => 'api/v1/project/' . $project->id,
                'method' => 'GET'
            ];
        }

        $response = [
            'msg' => 'List of all Projects',
            'projects' => $projects
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
            'title' => 'required',
            'description' => 'required'
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

        $title = $request->input('title');
        $description = $request->input('description');
        $sdate = $request->input('start_date');
        $cdate = $request->input('close_date');
        $user_id = $user->id;

        $project = new Project([

            'title' => $title,
            'description' => $description,
            'state_id' => 1,
            'user_id' => $user_id,
            'start_date' => $sdate,
            'close_date' => $cdate
        ]);
        if ($project->save()) {
            $project->view_project = [
                'href' => 'api/v1/project/' . $project->id,
                'method' => 'GET'
            ];
            $message = [
                'msg' => 'Project created',
                'project' => $project
            ];
            return response()->json($message, 201);
        }

        $response = [
            'msg' => 'Error during creationg'
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
        $project = Project::where('id', $id)->firstOrFail();
        $project->view_project = [
            'href' => 'api/v1/project',
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'project information',
            'project' => $project
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
            'title' => 'required',
            'description' => 'required'
        ]);

        if($validator->fails())
            return response()->json($validator->errors(), 404);

        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');

        // null check requred
        $project = Project::findOrFail($id);

        if ($project->user_id!=$user->id && $user->role_id!=1) {
            return response()->json(['msg' => 'This project is created by another user'], 401);
        };

        $project->title = $title;
        $project->description = $description;
        if (!$project->update()) {
            return response()->json(['msg' => 'Error during updating'], 404);
        }

        $project->view_project = [
            'href' => 'api/v1/project/' . $project->id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Project updated',
            'project' => $project
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
        //null check requred
        $project = Project::findOrFail($id);
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }
        //Only Admin can delete
        if ($user->role_id!=1) {
            return response()->json(['msg' => 'Your do not have permission to delete'], 401);
        };

        if (!$project->delete()) {
            return response()->json(['msg' => 'deletion failed'], 404);
        }

        $response = [
            'msg' => 'Project deleted',
            'create' => [
                'href' => 'api/v1/project',
                'method' => 'POST',
                'params' => 'title, description, time'
            ]
        ];

        return response()->json($response, 200);
    }
}
