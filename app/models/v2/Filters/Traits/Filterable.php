<?php

namespace v2\Filters\Traits;

use MIS;

use  Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{

    /**
     * @param Builder $builder
     * @param QueryFilter $filter
     */
    public function scopeFilter(Builder $builder, QueryFilter $filter)
    {
        $filter->apply($builder);
    }



    public static function InvokeQuery($sieve = [], $per_page = null, $full = false)
    {
        $query = self::latest();

        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = $per_page ?? 50;
        $skip = (($page - 1) * $per_page);

        $filter_class = self::$query_config['filter_class'];
        $pass_mark = self::$query_config['pass_mark'];
        $record_name = self::$query_config['name'];

        $filter = new $filter_class($sieve);

        $data = $query->Filter($filter)->count();

        $sql = $query->Filter($filter);

        $$record_name = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered

        $note = MIS::filter_note($$record_name->count(), $data, self::count(),  $sieve, $pass_mark, $full);
        return  get_defined_vars();
    }
}
