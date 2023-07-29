<?php


namespace Filters\Filters;

use Filters\QueryFilter;
use MIS;

/**
 * 
 */
class PaperFilter extends QueryFilter
{


    public function name($name = null)
    {

        if ($name == null) {
            return;
        }

        $this->builder->where('name', 'like', "%$name%");
    }

    public function id($id = null)
    {

        if ($id == null) {
            return;
        }

        $this->builder->where('id', strtolower($id));
    }


    public function editor_id($id = null)
    {
        if ($id == null) {
            return;
        }

        $this->builder->where('editor_id', $id);
    }





    public function date($date = null)
    {

        if ($date == null) {
            return;
        }

        $today = date("Y-m-d");
        switch ($date) {

            case 'this_week':
                $date = MIS::date_range($today, 'week', true);
                break;

            case 'last_week':
                $last_week = date("Y-m-d", strtotime("$today -1 week"));
                $date = MIS::date_range($last_week, 'week', true);
                break;

            case 'this_month':
                $date = MIS::date_range($today, 'month', true);
                break;

            case 'last_month':
                $last_month = date("Y-m-d", strtotime("$today -1 month"));
                $date = MIS::date_range($last_month, 'month', true);
                print_r($date);
                break;

            default:
                # code...
                break;
        }

        extract($date);
        $this->dateRange($start_date, $end_date);
    }


    public function dateRange($start_date = null, $end_date = null)
    {
        if (($start_date == null) && ($end_date == null)) {
            return;
        }

        $this->builder->whereDate('created_at', '>=',  $start_date)->whereDate('created_at', '<=', $end_date);
    }
}
