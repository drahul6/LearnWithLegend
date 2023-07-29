<?php
namespace v2\Models\Wp;

use  Filters\Traits\Filterable;

use Illuminate\Database\Eloquent\Model as Eloquent;
use wp\Models\Post;

class UserMeta extends Eloquent 
{
	use Filterable;
	
	protected $fillable = [
		'umeta_id',
		'user_id',
		'meta_key',
		'meta_value',
	];
								
	protected $table = 'wp_usermeta';
	protected $connection = 'wordpress';
	protected $primaryKey = 'umeta_id';


    const CREATED_AT = NULL;
    const UPDATED_AT = NULL;



	public function post()
	{
		return $this->belongsTo(Post::class, 'post_id');
	}




}