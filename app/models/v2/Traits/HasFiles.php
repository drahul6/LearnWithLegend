<?php

namespace v2\Traits;

use MIS;
use Upload;



/**
 * 
$product_information = MIS::refine_multiple_files($_FILES['product_information']);
$new_user->upload_files($product_information, null, 'uploads/tfiles', 'product_information');
*/
trait HasFiles 
{

	/* 
	public  $files_config = [
		'column'=> '', 
	]; */

	
	public function upload_files($files, $allowed_mimes=null, $directory=null, $shelf)
	{
	    $directory =  $directory == null ? 'uploads/files' : $directory;

	    foreach ($files as $key => $file) {
	        $handle = new Upload($file);
	        $file_type = explode('/', $handle->file_src_mime)[0];


	        if ($allowed_mimes != null) {
	            $handle->allowed = $allowed_mimes;
	        }


	        $label = MIS::random_string(10);
	        $handle->file_new_name_body = "{$this->table}_{$shelf}_$label";
	        $handle->Process($directory);

	        $file_path[$key] = [
	                'name' => $file['name_for_human'] ?? '',
	                'path' => $directory . '/' . $handle->file_dst_name 
	            ];
	    }

	    return $this->updateFilesArrayByKey($shelf, $file_path);
	    return false;
	}

	public function updateFilesArrayByKey($key, $value)
	{
	    $details = $this->FilesArray;
	    $old = ($details[$key]);
	    $final_value = $old;

	    foreach ($value as $new_key => $new_value) {
	        $final_value[$new_key] = $new_value;
	    }
	  
	    $details[$key] = $final_value;

		$column = $this->getFilesColumn();
		
	    $this->update([ $column => json_encode($details)]);
	}

	
	public function setFilesAttribute($value)
	{
	    $this->attributes['files'] = json_encode($value);
	}

	
	public function getFilesColumn()
	{
		return  self::$files_config['column'] ?? 'files';
	}


	public function getFilesArrayAttribute()
	{
		$column = $this->getFilesColumn();
	    if ($this->$column == null) {
	        return [];
	    }
	    return json_decode($this->$column, true);
	}




}