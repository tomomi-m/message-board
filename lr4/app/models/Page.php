<?php
class Page extends \Eloquent {
	
	// Add your validation rules here
	public static $rules = [ ];
	
	// Don't forget to fill this array
	protected $fillable = [ ];
	protected $guarded = array (
			'id' 
	);
	protected function getDateFormat() {
		return 'Y-m-d H:i:s';
	}
}