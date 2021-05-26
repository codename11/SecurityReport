<?php

namespace App\Http\Controllers\API;
use \stdClass;

class DateHelper {

    public $set_date;

    function __construct($set_date) {
        $this->set_date = $set_date;
    }

    function get_date() {
        return $this->set_date;
    }

    function datenames(){

        $obj = new stdClass();
        $str = strtotime($this->set_date);
        $obj->monthNum = date("m", $str);
        $obj->month = date("F", $str);
        $obj->year = date("Y", $str);
        $obj->numOfDays=cal_days_in_month(CAL_GREGORIAN,$obj->monthNum,$obj->year);

        $obj->dayNamesInMonthWithNums = [];
        for ($i = 1; $i <= $obj->numOfDays; $i++) {

            $obj->dayNamesInMonthWithNums[$i-1] = $i."/".substr(date("l", strtotime($i."-".$obj->monthNum."-".$obj->year)), 0, 3);
            
        }

        return $obj;

    }

}

/*$primer = new DateHelper("Apple");
echo $primer->get_date();*/
?>