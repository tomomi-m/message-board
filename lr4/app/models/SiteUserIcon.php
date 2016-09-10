<?php
class SiteUserIcon extends \Eloquent {
	
	// Add your validation rules here
	public static $rules = [ ];
	
	// Don't forget to fill this array
	protected $fillable = [ ];
	protected $guarded = [ ];
	protected function getDateFormat() {
		return 'Y-m-d H:i:s';
	}
}