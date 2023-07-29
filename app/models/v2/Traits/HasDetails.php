<?php

namespace v2\Traits;

/**
 * 
 */
trait HasDetails
{

	/* 
	public  $details_config = [
		'column'=> '', 
	]; */




	public function getDetailsColumn($column = null)
	{
		$default =  self::$details_config['column'] ?? 'details';

		return $column == null ? $default : $column;
	}

	public function updateDetailsByKey($key, $value, $column = null)
	{
		$details = $this->DetailsArray;
		$details[$key] = $value;
		$column = $this->getDetailsColumn($column);
		return $this->update([$column => ($details)]);
	}



	public function setDetailsAttribute($value)
	{
		$column = $this->getDetailsColumn();
		$this->attributes[$column] = json_encode($value);
	}



	public function getDetailsArrayAttribute($column = null)
	{

		$column = $this->getDetailsColumn($column);
		if ($this->$column == null) {
			return [];
		}

		$attribute = "{$column}_array";
		if (is_array($this->$column)) {
			return $this->$column;
		} else {
			return json_decode($this->$column, true);
		}
		// return $this->attributes[$attribute] =
	}
}
