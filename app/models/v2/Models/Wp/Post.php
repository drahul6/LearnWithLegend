<?php
namespace v2\Models\Wp;

use Config;
use v2\Models\Wp\User;
use v2\Models\Wp\Terms;
use v2\Models\Wp\PostMeta;
use  Filters\Traits\Filterable;
use v2\Models\Wp\TermsTaxonomy;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Post extends Eloquent 
{
	use Filterable;
	
	protected $fillable = [
		'ID',
		'post_author',
		'post_date',
		'post_date_gmt',
		'post_content',
		'post_title',
		'post_excerpt',
		'post_status',
		'comment_status',
		'ping_status',
		'post_password',
		'post_name',
		'to_ping',
		'pinged',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'post_parent',
		'guid',
		'menu_order',
		'post_type',
		'post_mime_type',
		'comment_count',
	];
								
	protected $table = 'wp_posts';
	protected $connection = 'wordpress';
	protected $primaryKey = 'ID';


    const CREATED_AT = 'post_date';
    const UPDATED_AT = 'post_modified';



	public static function categories()
	{
		$categories = TermsTaxonomy::Categories()->where('parent',0)->get();		
		
		$counts = TermsRelationship::select(DB::raw("count(*) as num"), 'term_taxonomy_id')
        ->groupBy('term_taxonomy_id')->get()->keyBy('term_taxonomy_id')->toArray();

		
		$domain = Config::domain();
		$categories->map(function($item) use ($domain, $counts){
			$slug = strtolower(str_replace(" ", "-", $item['term']['name']));
			$link = "$domain/blog/category/{$slug}";

			$item['link'] = $link;
			$item['num'] = $counts[$item['term_id']]['num'];
			return $item;
		});
		
		return $categories;
	}


	public function scopeRecent($query, $post=null)
	{
		$query->Published()->latest('post_date');
		if($post) {
			$query->where('ID', '!=', $post->ID);
		}
		return $query;
	}
	

	public function getfeaturedImageAttribute()
	{

	}


	public function getshortIntroAttribute()
	{
		return substr(strip_tags($this->post_content), 0, random_int(100, 150) );
	}


	public function geturlAttribute()
	{
		$domain = Config::domain();
		$date = date("Y/m/d", strtotime($this->post_date));
		$link = "$domain/blog/$date/id-{$this->ID}/$this->post_name";
		return $link;
	}
	
	public function author()
	{
		return $this->belongsTo(User::class, 'post_author');
	}

	public function scopePublished($query)
	{
        return  $query->where('post_type', 'post')->where('post_status', 'publish')->latest('post_date');
	}



/*
$related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null,
                                  $parentKey = null, $relatedKey = null, $relation = null*/
	public function terms_relationships()
	{
		return $this->belongsToMany( Terms::class, 'wp_term_relationships', 'object_id', 'term_taxonomy_id','ID','term_id');
	}


	public function quickview()
	{
		$quickview = <<<EL
		<h3> $this->post_title</h3> <br>
		<small>$this->post_excerpt</small>
		<hr>
		$this->post_content;

EL;

		return $quickview;
	}


	public function scopeOrder($query)
	{
		return $query->where('post_type', 'shop_order');
	}



	public function scopeCourses($query)
	{
		return $query->where('post_type', 'lp_course');
	}



	public function scopeCompleted($query)
	{
		return $query->where('post_status', 'wc-completed');
	}


	public function post_meta()
	{
		return $this->hasMany(PostMeta::class, 'post_id');
	}


	public function getattachmentsAttribute()
	{
		return self::where('post_parent', $this->ID)->where('post_type', 'attachment')->get();
	}


	public function order_items()
	{
		return $this->hasMany('wp\Models\WooOrderItem', 'order_id');
	}


}