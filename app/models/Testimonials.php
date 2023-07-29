<?php


use Illuminate\Database\Eloquent\Model as Eloquent;

use  Filters\Traits\Filterable;
use v2\Models\Sales;


class Testimonials extends Eloquent
{
	use Filterable;

	protected $fillable = [
		'attester',
		'user_id',
		'intro',
		'content',
		'details',
		'location',
		'type',
		'video_link',
		'approval_status',
		'published_status'
	];

	protected $table = 'testimonials';




	public function getDetailsArrayAttribute()
	{

		if ($this->details == null) {

			return [];
		}

		return  json_decode($this->details, true);
	}



	public function scopeApproved($query)
	{
		return $query->where('approval_status', 1);
	}


	public function scopeVideos($query)
	{
		return $query->where('type', 'video');
	}

	public function scopeTexts($query)
	{
		return $query->where('type', 'written');
	}


	public function scopeNotApproved($query)
	{
		return $query->where('approval_status', 0);
	}


	public function is_approved()
	{
		return $this->approval_status == 1;
	}


	public function user()
	{
		return $this->belongsTo('User', 'user_id');
	}

	public function getprofilepicAttribute()
	{
		if ($this->user == null) {
			$pix = Config::default_profile_pix();
		} else {

			$pix = $this->user->profilepic;
		}

		return $pix;
	}


	public function getDisplayStatusAttribute()
	{
		if ($this->approval_status) {
			$status = "<span class='badge badge-success'>Approved</span>";
		} else {

			$status = "<span class='badge badge-danger'>Not Approved</span>";
		}

		return $status;
	}

	public function isVideo()
	{
		return $this->type == 'video';
	}

	public function getDisplayPublishedStatusAttribute()
	{
		if ($this->published_status) {
			$status = "<span class='badge badge-success'>Published</span>";
		} else {

			$status = "<span class='badge badge-danger'>Not Published</span>";
		}

		return $status;
	}
}
