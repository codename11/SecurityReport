<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \stdClass;
use App\Calendar;
use App\Main_Heading;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
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
        if($request->ajax()){
            
            $calendar = Calendar::find($request->id) ? Calendar::with("user", "main_heading")->find($request->id) : null;
            
            if($calendar){

                $response = array(
                    "message" => "Found your calendar.",
                    "calendar" => $calendar
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
        if($request->ajax()){
            //Ovo bi trebalo da budu dodatna polja u ovoj tabeli.
            $validator = Validator::make($request->all(), [
                "employee_names" => "required|array|min:1",
                "employee_names.*" => "required|string|distinct|min:1",
                "mh_id" => "required|integer",
            ]);

            if($validator->fails()){

                $response["error"] = $validator->errors();
                $response["message"] = "Validation Error";

                return response($response);

            }
            $main_heading = Main_Heading::find($request->mh_id) ? Main_Heading::find($request->mh_id) : null;

            if($main_heading){

                $obj = new stdClass();
                $str = strtotime($main_heading->set_date);
                $obj->monthNum = date("m", $str);
                $obj->month = date("F", $str);
                $obj->year = date("Y", $str);

                $numOfDays=cal_days_in_month(CAL_GREGORIAN,$obj->monthNum,$obj->year);

                $obj->dayNamesInMonthWithNums = [];
                for ($i = 1; $i <= $numOfDays; $i++) {

                    $obj->dayNamesInMonthWithNums[$i-1] = $i."/".substr(date("l", strtotime($i."-".$obj->monthNum."-".$obj->year)), 0, 3);
                    
                }

                $calendar = new Calendar;
                $calendar->names = $request->employee_names;
                $calendar->daynums = $obj->dayNamesInMonthWithNums;
                $calendar->user_id = auth()->user()->id;
                $calendar->mh_id = $request->mh_id;
                $calendar->save();

                $cal = Calendar::with("user", "main_heading")->findOrFail($calendar->id);
                $response = array(
                    "message" => "You created a calendar!",
                    "cal" => $cal,
                );

                return response($response, 200);

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

}
