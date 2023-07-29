<?php

use v2\Tax\Tax;
use  v2\Models\Market;
use v2\Traits\HasStatus;
use v2\Filters\Traits\Filterable;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Products extends Eloquent
{

	use Filterable;
	use HasStatus;

	protected $fillable = [
		'name',
		'user_id',
		'price',
		'data',
		'stock',
		'category_id',
		'description',
		'front_image',
		'downloadable_files',
		'type_of_product',
		'status',
	];

	protected $table = 'products';


	protected $hidden = ['downloadable_files'];
	public static $category_in_market = 'product';

	public static $types_of_product = ['physical', 'digital'];
	public static $sortby = ['price'];


	//order stages, 1-draft, 2-submitted, 3-approved, 0-declined
	public static $statuses_config = [
		'use' => 'hierarchy',  //can be name or hierachy e.g draft or 1
		'column' => 'status',
		'push_url' => 'admin/product_status',  //ulr to update changes
		'use_hierarchy' => false,
		'states' => [
			[
				'name' => 'draft', //name of status e.g completed
				'hierarchy' => 1, //the hierachy  int e.g 1
				'color' => 'secondary',    //the color e.g warning
				'after_set' => null, // a function that will be called after setting this status
				'before_set' => null, // a function that will be called before setting this status
				'is_final' => false, // this status cannot be reversed
			],

			[
				'name' => 'in review',
				'hierarchy' => 2,
				'color' => 'warning',
				'after_set' => null,
				'before_set' => null,
				'is_final' => false,
			],
			[
				'name' => 'approved',
				'hierarchy' => 3,
				'color' => 'success',
				'after_set' => null,
				'before_set' => null,
				'is_final' => false,
			],
			[
				'name' => 'declined',
				'hierarchy' => 0,
				'color' => 'danger',
				'after_set' => null,
				'before_set' => null,
				'is_final' => false,
			],

		],
	];



	public static function getValidationRule($user_id, $product)
	{

		$validation_rule = [

			"composite_unique" => [
				"columns_value" => [
					"user_id" => $user_id,
					"name" => $product->name
				],
				"model" => Products::class,
				"name" => "Product",
				"primary_key" => 'id',
				"find_key" => $product->id,
			],

			'name' => [
				'required' => true,
				'min' => 2,
			],
			'type_of_product' => [
				'required' => true,
				'in' => Products::$types_of_product,
			],
			'price' => [
				'required' => true,
				'min_value' => 1,
				'numeric' => true,
			],

			'description' => [
				'required' => true,
				'min' => 4,
			],
		];


		return $validation_rule;
	}


	public function scopeApproved($query)
	{
		return $query->where('status', 3);
	}

	public function reduceStock($qty)
	{
		if ($this->stock == null) {
			return null;
		}

		return $this->decrement('stock', $qty);
	}

	public function scopeOnSale($query)
	{
		return $query->whereRaw("status=3 and ((stock is null) or (stock > 0))");
	}



	public function getimageJsonAttribute()
	{
		$value = $this->image;


		if ((!is_dir($value))  && (file_exists($value))) {

			return ($value);
		}

		return 'uploads/images/courses/course_image.jpeg';
	}



	public static function star_rating($rate,  $scale)
	{
		$stars = '';
		for ($i = 1; $i <= $scale; $i++) {
			if ($i <= $rate) {
				$stars .= "<i class='fa fa-star'></i>";
			} else {
				$stars .= "<i class='fa fa-star-o'></i>";
			}
		}

		$point = number_format(($rate), 1);
		$stars .= " (<b>$point</b>)";
		$star_rating = compact('rate', 'scale', 'stars', 'point');

		return $star_rating;
	}



	public function quickview()
	{
		return "";
		$currency = Config::currency();
		$price = MIS::money_format($this->price);
		$by = $this->seller->fullname ?? "n/a";

		$last_updated = date("M j, Y h:iA", strtotime($this->updated_at));

		$quickview = "
            <h5><b>{$this->name}</b></h5>
            <p> Location: Lagos | Delivery:Postage </p>
            <p>by $by <span style='margin-left: 30px;    font-weight: bold;  font-size: 25px;'> $currency$price</span>
            </p> 
            <hr>
            <p>$this->description</p>
            <ul>

            </ul>
         
          ";

		return $quickview;
	}

	public function scopeFree($query)
	{
		return $query->where('price', 0);
	}



	public function is_free()
	{
		return $this->price == 0;
	}


	public function tax_breakdown()
	{
		$tax = new Tax;
		$tax_payable  =	$tax->setTaxSystem('general_tax');
		return $tax->setProduct($this)
			->calculateApplicableTax()->amount_taxable;
	}



	public function getAdminDownloadLinkAttribute()
	{
		$domain = Config::domain();
		$singlelink = "$domain/admin/download_request/$this->id";
		return $singlelink;
	}

	public function getUserDownloadLinkAttribute()
	{
		$domain = Config::domain();
		$singlelink = "$domain/user/download_preview/$this->id";
		return $singlelink;
	}

	public function getViewLinkAttribute()
	{
		$domain = Config::domain();

		$url_friendly = MIS::encode_for_url($this->title);
		$category_in_market = self::$category_in_market;
		$singlelink = "$domain/shop/full-view/$this->id/$category_in_market/$url_friendly";

		return $singlelink;
	}
	public function getUserDeleteLinkAttribute()
	{
		$domain = Config::domain();

		$singlelink = "$domain/user/delete_product?item_id=$this->id";
		return $singlelink;
	}
	public function getBuyNowLinkAttribute()
	{
		$domain = Config::domain();

		$singlelink = "$domain/user/buy/$this->id";
		return $singlelink;
	}


	public function seller()
	{
		return $this->belongsTo(User::class, 'user_id');
	}


	function getQuickDescriptionAttribute()
	{

		return substr($this->description, 0, 250);
	}

	public function market_details()
	{

		$domain = Config::domain();
		$thumbnail = "$this->mainimage";
		// $tax = $this->tax_breakdown();
		$market_details = [
			'id' => $this->id,
			'model' => self::class,
			'name' => $this->name,
			'stock' => $this->stock,
			'short_name' => substr($this->name, 0, 34),
			'description' => $this->description,
			'short_description' => substr($this->description, 0, 50) . '...',
			'quick_description' => substr($this->description, 0, 250) . '...',
			'price' => $this->price,
			'old_price' => null,
			'by' => $this->seller->username ?? null,
			'star_rating' => self::star_rating(4, 5),
			'quickview' =>  $this->quickview(),
			'single_link' =>  $this->ViewLink,
			'buy_now_link' =>  $this->BuyNowLink,
			'thumbnail' =>  $thumbnail,
			// 'tax' =>  $tax,
		];

		return $market_details;
	}


	public function getProductCodeAttribute()
	{

		$substr = substr(strval(time()), 7);
		$code = "PLWG{$this->id}G{$substr}";

		return $code;
	}




	public function download()
	{
		$type = MIS::custom_mime_content_type($this->downloadable_files);
		$filename = end(explode('/', $this->downloadable_files));

		if (!file_exists($this->downloadable_files)) {
			Session::putFlash('danger', "could not fetch file");
			return;
		}

		header("Content-type: $type");
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		readfile($this->downloadable_files);
		exit();
	}



	public static function validate_cart($cart_items)
	{
		$errors = [];
		$totals = [];
		foreach ($cart_items as $key => $item) {
			$real_product =  self::find($item['id']);
			$totals[] = $real_product->price * $item['qty'];
			if (
				($real_product->price != $item['price'])
			) {
				$errors['price'] = "incorrect";

				return false;
			}

			return true;
		}
	}

	public function isPhysical()
	{

		return $this['type_of_product'] == 'physical';
	}


	public function getpercentdiscountAttribute()
	{
		if (($this->old_price == null) || ($this->old_price <= $this->price)) {
			return 0;
		}

		return  (int) (($this->old_price - $this->price) * (100 / $this->old_price));
	}


	public function is_ready_for_review()
	{
		return true;
	}

	public function update_product($inputs, $files, $downloadable_files)
	{


		if (Input::exists('')  || true) {
			$validator = new Validator;
			$validator->check(Input::all(), array(

				'name' => [
					'required' => true,
					'min' => 2,
				],
				'type_of_product' => [
					// 'required' => true,
					'in' => self::$types_of_product,
				],
				'price' => [
					// 'required' => true,
					'min_value' => 1,
					'numeric' => true,
				],
				'stock' => [
					// 'required' => true,
					'min_value' => 1,
					'numeric' => true,
				],

				'description' => [
					// 'required' => true,
					'min' => 4,
				],
				"composite_unique" => [
					"columns_value" => [
						"user_id" => $this->user_id,
						"name" => $inputs['name']
					],
					"model" => Products::class,
					"name" => "Product",
					"primary_key" => 'id',
					"find_key" => $this->id
				]

			));



			if (!$validator->passed()) {

				Session::putFlash('danger', Input::inputErrors());
				return;
			}


			DB::beginTransaction();
			try {
				$this->update([
					'name' 		=> $inputs['name'],
					'price' 	=> $inputs['price'],
					'category' 	=> $inputs['category_id'] ?? null,
					'description' => $inputs['description'],
					'type_of_product' => $inputs['type_of_product'] ?? null,
					'stock' => $inputs['stock'] == '' ? null : $inputs['stock'],
					'data' => $inputs['data'] ?? [],
				]);

				$this->update_product_images($files, $inputs['images_to_be_deleted'] ?? []);
				$this->upload_downloadable_files($downloadable_files);

				DB::commit();
				Session::putFlash('success', 'Changes Saved Successfully.');

				return true;
			} catch (Exception $e) {
				DB::rollback();
				Session::putFlash('danger', "Seems {$inputs['name']} already exist.");
				print_r($e->getMessage());
				return false;
			}
		}
	}



	public function upload_downloadable_files($file)
	{
		$directory = 'uploads/images/downloadable_files';


		$handle = new Upload($file);

		$handle->Process($directory);
		$file_path = $directory . '/' . $handle->file_dst_name;

		$this->update(['downloadable_files' => $file_path]);
		return ($file_path);
	}



	public static function upload_post_images($files)
	{
		$directory = 'uploads/images/products';


		$refined_file = MIS::refine_multiple_files($files);


		$i = 0;
		foreach ($refined_file as  $file) {

			$handle = new Upload($file);


			$file_type = explode('/', $handle->file_src_mime)[0];
			if (($file_type == 'image') || ($file_type == 'video')) {



				$min_height = 350;
				$min_width  = 263;



				$handle->Process($directory);
				$file_path = $directory . '/' . $handle->file_dst_name;

				if ($file_type == 'image') {

					// we now process the image a second time, with some other settings
					$handle->image_resize            = true;
					// $handle->image_ratio_y           = true;
					$handle->image_x                 = $min_width;
					$handle->image_y                 = $min_height;

					$handle->Process($directory);

					$resized_path    = $directory . '/' . $handle->file_dst_name;

					$images[$i]['main_image'] = $file_path;
					$images[$i]['thumbnail'] = $resized_path;
				}
			}
			$i++;
		}



		$property_media = [
			'images' => $images,
		];




		return ($property_media);
	}






	public function update_product_images($files, $images_to_be_deleted = [])
	{

		$property_media =	$this->upload_post_images($files);



		$new_images = $property_media['images'] ?? [];


		$previous_images =  $this->images['images'] ?? [];


		//delete necessary ones
		foreach ($images_to_be_deleted as $value) {
			$images_in_previous = $previous_images[$value];
			foreach ($images_in_previous as $image_path) {
				$handle =  new Upload($image_path);
				$handle->clean();
			}

			unset($previous_images[$value]);
		}
		($updated_previous_images = array_values($previous_images)); //after removing some

		if (array_values($previous_images) == null) {
			$updated_previous_images =  [];
		}

		foreach ($new_images as  $image) {
			array_unshift($updated_previous_images, $image);
		}





		$updated_files = [
			'images' => $updated_previous_images
		];

		$this->update(['front_image' => json_encode($updated_files)]);
	}




	public function getdeletelinkAttribute($value)
	{
		return  Config::domain() . "/admin-products/deleteProduct/{$this->id}";
	}


	public function related_products()
	{
		return	self::where('id', '!=', $this->id)
			->whereRaW("(category_id = '$this->category_id' OR id != $this->id )")
			->latest()->take(20)->get()->shuffle()->take(4);
	}


	public function getimagesAttribute()
	{
		if ($this->front_image == []) {
			return [];
		}
		return json_decode($this->front_image, true);
	}



	public static  function default_ebook_pix()
	{
		$logo = Config::logo();
		return "$logo";
	}


	public function getDisplayImageAttribute()
	{

		if (count($this->images['images']) <= 1) {
			$images = <<<ELM
<img loading="lazy" class="product-img img-responsive img-fluid" src="$this->mainimage">

ELM;

			return $images;
		}


		$images = <<<EL

<div id="demo" class="carousel slide" data-ride="carousel">

	<ul class="carousel-indicators">
		<li data-target="#demo" data-slide-to="0" class="active"></li>
		<li data-target="#demo" data-slide-to="1"></li>
		<li data-target="#demo" data-slide-to="2"></li>
	</ul>

	
	<div class="carousel-inner">

		
EL;

		foreach ($this->images['images'] as $key => $image) {
			$active = $key == 0 ? 'active' : '';
			$domain = Config::domain();
			$image_path = (!file_exists($image['thumbnail'])) ? Products::default_ebook_pix()
				: "$domain/{$image['thumbnail']}";
			$images .= <<<IM
				<div class="carousel-item $active">
					<img loading="lazy" src="$image_path" style="max-height:380px;" class="img-fluid img-responsive"  alt="{$this->name}">
				</div>

IM;
		}
		$images .= <<<EL

	</div>

	<!-- Left and right controls -->
	<a class="carousel-control-prev" href="#demo" data-slide="prev">
		<span class="carousel-control-prev-icon"></span>
	</a>
	<a class="carousel-control-next" href="#demo" data-slide="next">
		<span class="carousel-control-next-icon"></span>
	</a>

</div>
EL;

		return $images;
	}

	public function getmainimageAttribute()
	{
		$value =  @$this->images['images'][0]['main_image'] ?? '';

		if (!file_exists($value)) {
			return (self::default_ebook_pix());
		}

		$pic_path = Config::domain() . "/" . $value;
		return $pic_path;
	}



	public function getsecondaryimageAttribute()
	{
		if (($this->images['images'][1] != null) && (file_exists($this->images['images'][1]['main_image']))) {
			return $this->images['images'][1];
		}
		return $this->mainimage;
	}





	public function getregularpriceAttribute()
	{
		if ($this->old_price != '') {
			return  Config::currency() . ' ' . number_format($this->old_price, 2);
		}
	}




	public static function upload_product_images($files)
	{
		$directory = 'uploads/images/products';

		foreach ($files as $attribute => $attributes) {
			foreach ($attributes as $key => $value) {
				$refined_file[$key][$attribute] = $value;
			}
		}

		$i = 0;
		foreach ($refined_file as  $file) {

			$handle = new Upload($file);


			$file_type = explode('/', $handle->file_src_mime)[0];
			if (($file_type == 'image') || ($file_type == 'video')) {



				$min_height = 335;
				$min_width  = 270;

				// echo $handle->image_src_x;

				if (($handle->image_src_x < $min_width) || ($handle->image_src_y < $min_height)) {

					Session::putFlash('info', "Item image $i) must be or atleast {$min_width}px min 
								width x {$min_height}px min height for best fit!");
					continue;
				}


				$handle->Process($directory);
				$file_path = $directory . '/' . $handle->file_dst_name;

				if ($file_type == 'image') {

					// we now process the image a second time, with some other settings
					$handle->image_resize            = true;
					// $handle->image_ratio_y           = true;
					$handle->image_x                 = 350;
					$handle->image_y                 = 263;

					$handle->Process($directory);

					$resized_path    = $directory . '/' . $handle->file_dst_name;

					$images[$i]['main_image'] = $file_path;
					$images[$i]['thumbnail'] = $resized_path;
				}
			}
			$i++;
		}



		$property_media = [
			'images' => $images,
		];




		return ($property_media);
	}



	public function setDataAttribute($value)
	{
		$this->attributes['data'] = json_encode($value);
	}

	public function getDataAttribute($value)
	{
		if ($value == null) {
			return [];
		}

		return  json_decode($value, true);
	}


	public function quickdescription()
	{
		return substr($this->description, 0, random_int(240, 450)) . '...';
	}



	public function url_link()
	{
		return Config::domain() . "/shop/product_detail/{$this->id}/{$this->url_title()}";
	}


	public function url_title()
	{
		return str_replace(' ', '-', trim($this->name));
	}


	public  function is_on_sale()
	{
		return (bool)($this->on_sale == 1);
	}

	public static function on_sale()
	{
		return self::where('on_sale', 1);
	}

	public function category()
	{
		return $this->belongsTo('ProductsCategory', 'category_id');
	}
}
