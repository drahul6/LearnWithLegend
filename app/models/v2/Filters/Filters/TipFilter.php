<?php


namespace Filters\Filters;

use Filters\QueryFilter;
use MIS, User;
use v2\Models\Paper;

use Filters\Traits\RangeFilterable;

/**
 * 
 */
class TipFilter extends QueryFilter
{

    use RangeFilterable;


    public function round($round = null)
    {
        if ($round == null) {
            return;
        }
    }



    public function no_of_pair($no_of_pair = null)
    {

        if ($no_of_pair == null) {
            return;
        }


        $identfier = 'no_of_pair":"' . $no_of_pair;

        $identfier = trim($identfier);

        $this->builder->where('detail', 'like', "%$identfier%");
    }


    public function no_of_bankers($no_of_bankers = null)
    {

        if ($no_of_bankers == null) {
            return;
        }


        $identfier = 'no_of_bankers":"' . $no_of_bankers;


        $identfier = trim($identfier);

        $this->builder->where('detail', 'like', "%$identfier%");
    }



    public function wk_of_operation($wk_of_operation = null)
    {

        if ($wk_of_operation == null) {
            return;
        }


        $this->builder->where('weeks_of_operations', $wk_of_operation);
    }


    public function name($name = null)
    {

        if ($name == null) {
            return;
        }

        $paper_ids = Paper::where("name", "like", "%$name%")->get()->pluck('id')->toArray();

        $this->builder->whereIn('paper_id', $paper_ids);
    }


    public function id($id = null)
    {

        if ($id == null) {
            return;
        }

        $id = explode(',', $id);

        $this->builder->whereIn('id', $id);
    }





    public function editorId($id = null)
    {
        if ($id == null) {
            return;
        }

        $this->builder->where('editor_id', $id);
    }



    public function editor($name = null)
    {
        if ($name == null) {
            return;
        }

        $user_ids = User::WhereRaw(
            "firstname like ? 
	                                      OR lastname like ? 
	                                      OR username like ? 
	                                      OR email like ? 
	                                      OR phone like ? 
	                                      ",
            array(
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%'
            )
        )->get()->pluck('id')->toArray();



        $this->builder->whereIn('editor_id', $user_ids);
    }


    public function email($email = null)
    {

        if ($email == null) {
            return;
        }

        $user_ids = \User::where('email', 'like', "%$email%")->get()->pluck('id')->toArray();

        $this->builder->whereIn('user_id', $user_ids);
    }
}
