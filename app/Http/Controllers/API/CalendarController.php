<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \stdClass;
use App\Calendar;
use App\Main_Heading;
use App\User;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Calendar::class);
        if($request->ajax()){
            
            $calendars = Calendar::all() ? Calendar::with("user", "main_heading")->get() : null;
            
            $response = array(
                "message" => "List of all calendars with their creators and headings.",
                "calendars" => $calendars
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

    public function show(Request $request)
    {
        $this->authorize('viewAny', Calendar::class);
        if($request->ajax()){
            
            $calendar = Calendar::find($request->id) ? Calendar::with("user", "main_heading")->find($request->id) : null;
            
            $employee_ids = [];
            for($i=0;$i<count($calendar->employee_shifts);$i++){

                $employee_ids[$i] = $calendar->employee_shifts[$i]["employee_id"];

            }
            $calendar->employees = User::findMany($employee_ids);
            
            if($calendar){

                $response = array(
                    "message" => "Found your calendar.",
                    "calendar" => $calendar,
                );
                
                return response()->json($response);

            }
            else{

                $response = array(
                    "message" => "Can't find your calendar.",
                    "calendar" => $calendar
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

    public function store(Request $request)
    {
        $this->authorize('create', Calendar::class);
        if($request->ajax()){
            //Ovo bi trebalo da budu dodatna polja u ovoj tabeli.
            $validator = Validator::make($request->all(), [
                "mh_id" => "required|integer",
                "employee_shifts" => "required|array",
                "employee_shifts.*.employee_id" => "required|integer",
                "employee_shifts.*.shifts" => "required|array",
                "employee_shifts.*.shifts.*" => [
                    "required",
                    "integer",
                    function ($attribute, $value, $fail){
                        //Nulte vrednosti u smeni radnika su neradni dani.
                        if($value !== 1 && $value !== 2 && $value !== 0){
                            $fail('The '.$attribute.' is invalid.');
                        }
                    }
                ],
            ]);

            if($validator->fails()){

                $response["error"] = $validator->errors();
                $response["message"] = "Validation Error";

                return response($response);

            }
            $main_heading = Main_Heading::find($request->mh_id) ? Main_Heading::find($request->mh_id) : null;

            $dateHelper = new DateHelper($request->set_date);
            $setDate = $dateHelper->get_date();
            $obj = $dateHelper->datenames();

            if($main_heading){

                //First check. Check if calendar already exists.
                $checkIfcalExists = Calendar::where("mh_id", "=", $request->mh_id)->first();

                //Second check.
                $arr1 = [];//Checks if entered employee shifts exceeds or doesn't have enough days in month.
                //If days for any employee doesn't check out, it'll show an error and resulted calendar wouldn't be stored.
                for($i=0;$i<count($request->employee_shifts);$i++){

                    if(count($request->employee_shifts[$i]["shifts"]) != count($obj->dayNamesInMonthWithNums)){
                        //Logs an error.
                        $arr1[$i] = new stdClass();
                        $arr1[$i]->message1 = "Employee with id ".$request->employee_shifts[$i]["employee_id"]." doesn't have proper quantity of shifts";
                        $arr1[$i]->message2 = "Quantity needed ".count($obj->dayNamesInMonthWithNums).", quanity had ".count($request->employee_shifts[$i]["shifts"]).".";
                    
                    }

                }

                $checkShifts = count($arr1) > 0 ? true : false;

                if($checkIfcalExists || $checkShifts==true){

                    $errors = [];
                    if($checkShifts==true){

                        $errors[0] = new stdClass();
                        $errors[0]->message = "Shift placements aren't Ok!";
                        $errors[0]->List_of_employees_that_dont_have_proper_shifts = $arr1;

                    }

                    if($checkIfcalExists){
                        $errors[1] = new stdClass();
                        $errors[1]->message = "Calendar already exists!";
                    }

                    $response = array(
                        count($errors)>1 ? "Errors" : "Error " => $errors,
                    );

                    return response($response, 200);
                    
                }
                else{

                    $calendar = new Calendar;
                    $calendar->employee_shifts = $request->employee_shifts;
                    $calendar->daynums = $obj->dayNamesInMonthWithNums;
                    $calendar->user_id = auth()->user()->id;
                    $calendar->mh_id = $request->mh_id;

                    $calendar->save();

                    $cal = Calendar::with("user", "main_heading")->findOrFail($calendar->id);
                    $response = array(
                        "message" => "You created a calendar!",
                        "cal" => $cal
                    );

                    return response($response, 200);

                }

            }
            else{

                $response = array(
                    "message" => "Error: Main Heading doesn't exist.",
                );

                return response($response, 200);

            }

        }
        else{

            $response = array(
                "message" => "Not an ajax",
            );
            
            return response()->json($response);

        }

    }

    public function update(Request $request)
    {
        $this->authorize('update', Calendar::class);
        if($request->ajax()){
            //Ovo bi trebalo da budu dodatna polja u ovoj tabeli.
            $validator = Validator::make($request->all(), [
                "cal_id" => "required|integer",
                "employee_shifts" => "required|array",
            ]);

            if($validator->fails()){

                $response["error"] = $validator->errors();
                $response["message"] = "Validation Error";

                return response($response);

            }
            $ifCalExists = Calendar::find($request->cal_id) ? Calendar::find($request->cal_id) : null;

            if($ifCalExists){
                
                $arrCheck = [];//Checks if entered employee shifts exceeds days in month.
                //If days for any employee doesn't check out, it'll show an error and resulted calendar wouldn't be stored.
                for($i=0;$i<count($request->employee_shifts);$i++){

                    if(count($request->employee_shifts[$i]["shifts"]) >= count($ifCalExists->daynums)){
                        $arrCheck[$i] = true;
                    }
                    else{
                        $arrCheck[$i] = false;
                    }

                }
                $checkSfits = array_reduce($arrCheck, function (bool $acc, $value) {
                    return !$acc ? $acc : $value === null;
                }, true);

                if($checkSfits === false){

                    $ifCalExists->employee_shifts = $request->employee_shifts;
                    $ifCalExists->save();
                    $response = array(
                        "message" => "Shifts Updated",
                        "ifCalExists" => $ifCalExists
                    );
    
                    return response($response, 200);

                }
                else{

                    $response = array(
                        "message" => "Shift placements aren't Ok!",
                        "cal" => $cal
                    );

                    return response($response, 200);

                }

            }
            else{

                $response = array(
                    "message" => "Error: Calendar doesn't exist.",
                );

                return response($response, 200);

            }

        }
        else{

            $response = array(
                "message" => "Not an ajax",
            );
            
            return response()->json($response);
            
        }

    }

    public function destroy(Request $request)
    {
        $this->authorize('delete', Calendar::class);
        if($request->ajax()){

            $validator = Validator::make($request->all(), [
                "id" => "required|integer",
            ]);

            if($validator->fails()){

                $response["error"] = $validator->errors();
                $response["message"] = "Validation Error";

                return response($response);

            }

            $calendar = Calendar::find($request->id) ? Calendar::find($request->id) : null;

            if($calendar){

                $calendar->delete();

                $response = array(
                    "message" => "Calendar succefully deleted.",
                );
                
                return response()->json($response);

            }
            else{

                $response = array(
                    "message" => "Can't find Calendar.",
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

}
