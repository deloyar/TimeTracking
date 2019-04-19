<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Effort;
use App\User;
use App\Task;
use App\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Validator;
use JWTAuth;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth',
            ['only' => ['create'] ]
        );
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',

        ]);

        if($validator->fails())
            return response()->json($validator->errors(), 404);

        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        $startdate = date($request->input('start_date'));
        $enddate = date($request->input('end_date'));

        $q ='select * from users u left outer join efforts e on u.id=e.user_id
left outer join tasks t on e.task_id=t.id left outer join 
projects p on t.project_id=p.id where e.date >=\''.$startdate.'\' and e.date <=\''.$enddate.'\'';

        $d = DB::select($q);

        //This eager loading is great but don't know how to use where on
        //efforts table
        $users = User::with('efforts.task.project')->get();

        $data = ['users'  => $users];


        return $data;


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
