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

    public function test(Request $request){
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
            $table->timestamps();
        });*/

        return response($response, 200);
    }

    public function index(Request $request)
    {

        $this->authorize('viewAny', Main_Heading::class);
        if($request->ajax()){

            $main_headings = Main_Heading::with("user")->get();
            $response = array(
                "message" => "Displaying all Main Headings",
                "main_headings" => $main_headings
            );
            
            return response()->json($response);

        }
        else{
            $response = array(
                "message" => "Not an ajax",
            );
            
            return response()->json($response);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Main_Heading::class);
        if($request->ajax()){

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

            $ifDate = Main_Heading::where("set_date", "=", $request->set_date);
            $dateStr1 = $ifDate && $ifDate->first(["set_date"]) ? Main_Heading::where("set_date", "=", $request->set_date)->first(["set_date"])->set_date : null;
            $dateStr2 = $request->set_date;
            
            if($dateStr1!=$dateStr2){

                $main_heading = new Main_Heading;
                $main_heading->obj_name = $request->obj_name;
                $main_heading->sec_comp_name = $request->sec_comp_name;
                $main_heading->set_date = $request->set_date;
                $main_heading->user_id = auth()->user()->id;
                $main_heading->save();
                
                $response = array(
                    "message" => "Main heading is created for ".$request->set_date." date for object ".$request->obj_name." by company ".$request->sec_comp_name,
                    "main_heading" => $main_heading->with("user")->get(),
                );
                
                return response()->json($response);

            }
            else{

                $response = array(
                    "message" => "There is already main heading for that date",
                    "main_heading" => $main_heading->with("user")->get(),
                );
                
                return response()->json($response);

            }

        }
        else{
            $response = array(
                "message" => "Not an ajax",
            );
            
            return response()->json($response);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Main_Heading  $main_Heading
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $this->authorize('viewAny', Main_Heading::class);
        if($request->ajax()){

            $validator = Validator::make($request->all(), [
                "id" => "required|integer",
            ]);

            if($validator->fails()){

                $response["error"] = $validator->errors();
                $response["message"] = "Validation Error";

                return response($response);

            }

            $main_heading = Main_Heading::findOrFail($request->id);

            $response = array(
                "message" => "This is Main Heading for date ".$main_heading->set_date.".",
                "main_heading" => $main_heading->with("user")->get(),
            );
            
            return response()->json($response);

        }
        else{
            $response = array(
                "message" => "Not an ajax",
            );
            
            return response()->json($response);
        }

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
        $this->authorize('update', Main_Heading::class);
        if($request->ajax()){

            $validator = Validator::make($request->all(), [
                "id" => "required|integer",
                "obj_name" => "required|max:255",
                "sec_comp_name" => "required|max:255",
                "set_date" => "required|date|after_or_equal:today",
            ]);

            if($validator->fails()){

                $response["error"] = $validator->errors();
                $response["message"] = "Validation Error";

                return response($response);

            }

            $main_heading = Main_Heading::findOrFail($request->id);
            $main_heading->obj_name = $request->obj_name;
            $main_heading->sec_comp_name = $request->sec_comp_name;
            $main_heading->set_date = $request->set_date;
            $main_heading->save();

            $response = array(
                "message" => "Main Heading succefully updated.",
                "main_heading" => $main_heading->with("user")->get(),
            );
            
            return response()->json($response);

        }
        else{
            $response = array(
                "message" => "Not an ajax",
            );
            
            return response()->json($response);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Main_Heading  $main_Heading
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $this->authorize('delete', Main_Heading::class);
        if($request->ajax()){

            $validator = Validator::make($request->all(), [
                "id" => "required|integer",
            ]);

            if($validator->fails()){

                $response["error"] = $validator->errors();
                $response["message"] = "Validation Error";

                return response($response);

            }

            $main_heading = Main_Heading::findOrFail($request->id);
            $main_heading->delete();

            $response = array(
                "message" => "Main Heading succefully deleted.",
            );
            
            return response()->json($response);

        }
        else{
            $response = array(
                "message" => "Not an ajax",
            );
            
            return response()->json($response);
        }

    }

}
