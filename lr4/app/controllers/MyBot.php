<?php
class MyBot {
	private static $BOTS = [ 
			'Googlebot',
			'Yahoo! Slurp',
			'msnbot' 
	];
	public static function isBotRequest() {
		$ua = Request::header ( 'User-Agent' );
		foreach ( self::$BOTS as $bot ) {
			if (strpos ( $ua, $bot )) {
				return true;
			}
		}
		return false;
	}
}