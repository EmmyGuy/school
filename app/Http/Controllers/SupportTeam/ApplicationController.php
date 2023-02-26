<?php

namespace App\Http\Controllers\SupportTeam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Application;

use DB;
use Datatables;
use Auth;


class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        if(request()->ajax())
        {
           
            if(request()->ajax())
            {

                $data = DB::table('applications')->get();

                // dd($data);

                return Datatables::of($data)
                        ->addColumn('action', function($data){
                            $button = '<button type="button" name="edit" id="'.$data->id.'" data-id="'.$data->id.'"class="edit-application btn btn-primary btn-sm">Edit</button>';
                            $button .= '&nbsp';'&nbsp';
                            $button .= '<button type="button" name="delete" id="'.$data->id.'" data-id="'.$data->id. '"class="delete btn btn-danger btn-sm">Delete</button>';
                            // $button .= '<button type="button" name="detail" id="'.$data->id.'" class="detail btn btn-success btn-sm">Detail</button>';
                            return $button;
                        })
                        ->make(true);
            }
        }
        // $sessions = DB::table('sessions')->pluck('name', 'id');
        // $appraisalTypes = DB::table('appraisal_types')->pluck('name', 'id');
        
        return view('pages.support_team.application.index');
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
        //
        if(request()->ajax())
            {
                 //
                //   dd(\Carbon\Carbon::parse($request->openning_date)->format('Y-m-d'));
                 
                try {
                    $AppId = $request->id;

                    $street   =   Application::updateOrCreate(['id' => $AppId],
                        ['id' => $AppId,
                         'session' => $request->current_session,
                         'name' => $request->name,
                         'amount' => $request->amount,
                         'status' => $request->status,
                         'openning_date' => \Carbon\Carbon::parse($request->openning_date)->format('Y-m-d'),
                         'closing_date' => \Carbon\Carbon::parse($request->closing_date)->format('Y-m-d'),
                         'applicant_type' => $request->application_category,
                        ]);

                } catch (Throwable $e) {
                    report($e);
            
                    return response()->json(false);
                }

                return response()->json(true);
            }

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
        $data = DB::table('applications')->where('id', $id)->first();
        // dd($data);
        return response()->json([
            'id' => $data->id,
            'session' => $data->session,
            'name' => $data->name,
            'amount' => $data->amount,
            'applicant_type' => $data->applicant_type,
            'closing_date' => \Carbon\Carbon::parse($data->openning_date)->format('m-d-Y'),
            'openning_date' => \Carbon\Carbon::parse($data->openning_date)->format('m-d-Y'),
            'status' => $data->status,
            ]);
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
        try {

            DB::table('applications')->where('id', $id)->delete();
        } catch (Throwable $e) {
            report($e);
    
            return response()->json([
                'message' => false
                ]);
        }

        return response()->json([
            'message' => true
            ]);
    }
}
