<?php


namespace v2\Filters\Filters;

use Filters\QueryFilter;
use User, Orders;
use Filters\Traits\RangeFilterable;



/**
 * 
 */
class ProductsFilters extends QueryFilter
{
    use RangeFilterable;





    public function location($country = null)
    {

        if ($country == null) {
            return;
        }

        $this->builder->where('data', 'like', "%$country%");
    }


    public function type_of_product($type_of_product = null)
    {

        if ($type_of_product == null) {
            return;
        }
        $this->builder->where('type_of_product', "$type_of_product");
    }





    public function status($status = null)
    {
        if ($status == null) {
            return;
        }

        $this->builder->where('status', $status);
    }


    public function user($user = null)
    {
        if ($user == null) {
            return;
        }

        $user_ids =  User::WhereRaw(
            "firstname like ? 
	                                      OR lastname like ? 
	                                      OR email like ? 
	                                      OR phone like ? 
	                                      OR username like ? 
	                                      ",
            array(
                '%' . $user . '%',
                '%' . $user . '%',
                '%' . $user . '%',
                '%' . $user . '%',
                '%' . $user . '%'
            )
        )->get()->pluck('id')->toArray();

        $this->builder->whereIn('user_id', $user_ids);
    }




    public function name($name = null)
    {

        if ($name == null) {
            return;
        }
        $this->builder->where('name', 'like', "%$name%");
    }



    public function sort($by = null, $direction = null)
    {

        if (($by == null) ||  ($direction == null)) {
            return;
        }

        $resolution = [
            "descending" => "desc",
            "ascending" => "asc",
        ];

        $dir = $resolution[$direction];

        $this->builder->orderBy($by, $dir);
    }






    public function price($start = null, $end = null)
    {

        if (($start == null) &&  ($end == null)) {
            return;
        }

        $volume = compact('start', 'end');

        if ($end == null) {
            $end = $start;
        }

        $end = $end;
        $start = $start;

        $this->Range($start, $end,  'price');
    }




    public function created_at($start_date = null, $end_date = null)
    {

        if (($start_date == null) &&  ($end_date == null)) {
            return;
        }

        $date = compact('start_date', 'end_date');

        if ($end_date == null) {
            $date = $start_date;
        }

        $this->date($date, 'created_at');
    }
}
