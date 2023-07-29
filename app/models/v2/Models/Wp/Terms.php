<?php
namespace v2\Models\Wp;

use  Filters\Traits\Filterable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use wp\Models\Post;
use Config;

class Terms extends Eloquent 
{
	use Filterable;
	
	protected $fillable = [

		'term_id',
		'name',
		'slug',
		'term_group',

	];
								
	protected $table = 'wp_terms';
	protected $connection = 'wordpress';
	protected $primaryKey = 'term_id';


    const CREATED_AT = NULL;
    const UPDATED_AT = NULL;







	public function scopeLevels($query)
	{
		return $query->where('name','like' ,'level%');
	}


	public function geturlAttribute()
	{
		$domain = Config::domain();
		$date = date("Y/m/d", strtotime($this->post_date));
		$link = "$domain/blog/category/{$this->term_id}/$this->slug";
		return $link;
	}


/*
$related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null,
                                  $parentKey = null, $relatedKey = null, $relation = null*/

	public function terms_relationships()
	{
		return $this->belongsToMany(Post::class, 'wp_term_relationships', 'term_taxonomy_id','object_id' ,'term_id', 'ID');
	}


}