<?php


use v2\Models\Company;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Admin extends Eloquent
{

	protected $fillable = ['username', 'firstname', 'lastname', 'phone', 'email', 'password', 'remember_token', 'blocked_on'];

	protected $table = 'administrators';

	protected $hidden = ['password'];


	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id');
	}

	public function getAdminViewUrlAttribute()
	{

		return  Config::domain() . "/admin/profile/" . $this->id;
	}




	public function is_owner()
	{

		return ($this->id == 1);
	}

	public function administrators()
	{
		return Admin::where('id', '!=', 1)->get();
	}



	public function getprofilepicAttribute()
	{
		$value = $this->profile_pix;
		if (!file_exists($value) &&  (!is_dir($value))) {
			return (Config::default_profile_pix());
		}

		return $value;
	}




	public function getfullnameAttribute()
	{

		return "{$this->lastname} {$this->firstname}";
	}


	/**
	 * is_blocked() tells whether a user is blocked or not
	 * @return boolean true when blocked and false ff otherwise
	 */
	public function is_blocked()
	{
		return	boolval($this->blocked_on);
	}



	/**
	 * eloquent mutators for password hashing
	 * hashes user password on insert or update
	 *@return 
	 */
	public function setPasswordAttribute($value)
	{
		$this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
	}
}
