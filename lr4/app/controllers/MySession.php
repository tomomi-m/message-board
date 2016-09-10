<?php
class MySession {
	public static function logout($siteId) {
		Session::forget ( $siteId . '_isHuman' );
		Session::forget ( $siteId . '_userName' );
		Session::forget ( $siteId . '_userId' );
	}
	public static function getUser($siteId) {
		if (! $siteId)
			App::abort ( 404, "Unvalid siteId" );
		
		$userId = Session::get ( $siteId . '_userId' );
		return $userId ? SiteUser::find ( $userId ) : null;
	}
	static function newUser($siteId, $userName) {
		if (! $siteId || ! $userName)
			App::abort ( 404, "Unvalid siteId" );
		$siteUser = SiteUser::where ( 'site', $siteId )->where ( 'userName', $userName )->first ();
		if (! $siteUser) {
			$siteUser = new SiteUser ();
			$siteUser->site = $siteId;
			$siteUser->userName = $userName;
			$siteUser->save ();
		}
		Session::put ( $siteId . '_userId', $siteUser->id );
		Session::put ( $siteId . '_userName', $siteUser->userName );
		return $siteUser;
	}
	public static function getUserId($siteId) {
		$userId = Session::get ( $siteId . '_userId' );
		return $userId;
	}
	public static function getUserName($siteId) {
		$userName = Session::get ( $siteId . '_userName' );
		
		// 移行
		Session::forget ( $siteId . '_isHuman' );
		$userId = Session::get ( $siteId . '_userId' );
		if ($userId)
			return $userName;
		
		if (! $userName) {
			$userName = Cookie::get ( $siteId . '_userName' );
			Cookie::queue ( Cookie::forget ( $siteId . '_userName' ) );
		}
		if (! $userName) {
			$userName = Cookie::get ( 'userName' );
			Cookie::queue ( Cookie::forget ( 'userName' ) );
		}
		
		if ($userName) {
			$userName = trim ( $userName );
		}
		if (! $userName)
			return null;
		
		return self::newUser ( $siteId, $userName )->userName;
	}
	public static function updateUserName($siteId, $userName) {
		$user = MySession::getUser ( $siteId );
		$user->userName = $userName;
		$user->save ();
		Session::put ( $siteId . '_userName', $userName );
		return $user;
	}
	public static function login($siteId, $userName) {
		$userName = trim ( $userName );
		$siteUser = SiteUser::where ( 'site', $siteId )->where ( 'userName', $userName )->first ();
		self::newUser ( $siteId, $userName );
		return ($siteUser != null);
	}
	public static function validateLogin($siteId) {
		$isHuman = MySession::getUserId ( $siteId );
		if ($isHuman)
			return null;
		
		$response = Response::make ( 'Unauthorized', 403 );
		return $response;
	}
}