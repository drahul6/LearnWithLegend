<?php

namespace v2\Filters\Filters;

use v2\Models\Wp\Terms;
use Filters\QueryFilter;
use Filters\Traits\RangeFilterable;
use v2\Models\Wp\TermsRelationship;

/**
 * 
 */
class PostFilter extends QueryFilter
{
    use RangeFilterable;



    public function category($category = null)
    {
        if ($category == null) {
            return;
        }


        $name = str_replace("-", " ", $category);
        $category = Terms::where('name', $name)->first();

        $posts_in_category = TermsRelationship::where('term_taxonomy_id', $category['term_id'])
            ->get()->pluck('object_id')->toArray();
            
        $this->builder->whereIn('id', $posts_in_category);

    }

    public function ref($ref = null)
    {

        if ($ref == null) {
            return;
        }

        $ref = explode(',', $ref);

        $this->builder->whereIn('id', $ref);
    }





    public function registration($start_date = null, $end_date = null)
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
