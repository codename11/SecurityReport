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
            $main_heading = Main_Heading::findOrFail($request->mh_id);
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
            $calendar->save();

            $c1 = Calendar::with("user")->findOrFail($calendar->id);
            $response = array(
                "message" => "bravo",
                "c1" => $c1,
                //"obj" => $obj,
                //"main_heading" => $main_heading,
                //"user" => auth()->user(),
            );

            return response($response, 200);

        }
        else{
            $response = array(
                "message" => "Not an ajax",
            );
            
            return response()->json($response);
        }

    }

}
