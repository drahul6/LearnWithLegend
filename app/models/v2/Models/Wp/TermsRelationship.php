<?php

namespace v2\Models\Wp;

use Config;
use wp\Models\Post;
use v2\Models\Wp\Terms;
use  Filters\Traits\Filterable;
use Illuminate\Database\Eloquent\Model as Eloquent;

class TermsRelationship extends Eloquent
{
    use Filterable;

    protected $fillable = [

        'object_id',
        'term_taxonomy_id',
        'term_order',
    ];

    protected $table = 'wp_term_relationships';
    protected $connection = 'wordpress';

    protected $primaryKey = 'object_id';


    const CREATED_AT = NULL;
    const UPDATED_AT = NULL;


}
