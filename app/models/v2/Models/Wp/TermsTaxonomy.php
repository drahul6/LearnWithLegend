<?php
namespace v2\Models\Wp;

use Config;
use wp\Models\Post;
use v2\Models\Wp\Terms;
use  Filters\Traits\Filterable;
use Illuminate\Database\Eloquent\Model as Eloquent;

class TermsTaxonomy extends Eloquent 
{
	use Filterable;
	
	protected $fillable = [

        'term_taxonomy_id',
        'term_id',
        'taxonomy',
        'description',
        'parent',
        'count',
	];
								
	protected $table = 'wp_term_taxonomy';
	protected $connection = 'wordpress';

	protected $primaryKey = 'term_taxonomy_id';


    const CREATED_AT = NULL;
    const UPDATED_AT = NULL;


    public function scopeCategories($query)
    {
        return $query->where('taxonomy','category')->with('term');
    }

    public function scopeTags($query)
    {
        return $query->where('taxonomy','post_tag');
    }

    public function term()
    {
        return $this->belongsTo(Terms::class, 'term_id');
    }

}