<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Main_Heading;
use Illuminate\Http\Request;
use \stdClass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class Main_HeadingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        

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
            "obj_name" => "required|max:255",
            "sec_comp_name" => "required|max:255",
            "set_date" => "required|date|after_or_equal:today",
        ]);

        if($validator->fails()){

            $response["error"] = $validator->errors();
            $response["message"] = "Validation Error";

            return response($response);

        }

        $obj = new stdClass();
        $str = strtotime($request->set_date);
        $obj->monthNum = date("m", $str);
        $obj->month = date("F", $str);
        $obj->year = date("Y", $str);

        $numOfDays=cal_days_in_month(CAL_GREGORIAN,$obj->monthNum,$obj->year);

        $obj->dayNamesInMonthWithNums = [];
        for ($i = 1; $i <= $numOfDays; $i++) {

            $obj->dayNamesInMonthWithNums[$i-1] = new stdClass();
            $obj->dayNamesInMonthWithNums[$i-1]->dayName = date("l", strtotime($i."-".$obj->monthNum."-".$obj->year));
            $obj->dayNamesInMonthWithNums[$i-1]->dayNum = $i;
            
        }

        $response = array(
            "message" => "bravo",
            "obj" => $obj,
            "test" => $obj->dayNamesInMonthWithNums[0],
            "requestAll" => $request->all(),
            "user" => auth()->user()
        );
        //Pre pravljenja tabele, proveriti po datumu koji treba biti unet u tabelu, da li vec postoji tabela sa tim datumom.
        /*Schema::connection('mysql')->create('tableName', function($table)
        {
            $table->increments('id');
        });*/

        return response($response, 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Main_Heading  $main_Heading
     * @return \Illuminate\Http\Response
     */
    public function show(Main_Heading $main_Heading)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Main_Heading  $main_Heading
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Main_Heading $main_Heading)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Main_Heading  $main_Heading
     * @return \Illuminate\Http\Response
     */
    public function destroy(Main_Heading $main_Heading)
    {
        //
    }
}
