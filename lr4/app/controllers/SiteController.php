<?php
class SiteController extends \BaseController {
	function postLogin(Site $site) {
		$rules = array (
				'captcha' => array (
						'required',
						'captcha'
				)
		);
		$data = Input::all ();
		$userName = trim ( htmlspecialchars ( $data ['userName'], ENT_COMPAT, 'UTF-8' ) );
		if (! $userName || Validator::make ( $data, $rules )->fails ()) {
			$response = Response::make ( '{"r":"are you human?"}', 202 );
		} else {
			$isExistUser = MySession::login ( $site->id, $userName );
			$response = Response::json ( array (
					"r" => "you are human",
					"isExistUser" => $isExistUser,
					"siteName" => $site->title,
					"userName" => MySession::getUserName ( $site->id )
			) );
		}
		return $response;
	}
	public function anyIsUserLogedIn(Site $site) {
		return Response::json ( array (
				"userName" => MySession::getUserName ( $site->id )
		) );
	}
	public function anyLogout(Site $site) {
		MySession::logout ( $site->id );
		return Response::json ( array (
				"userName" => ""
		) );
	}
	public function getIko1(Site $site) {
		MySession::logout ( $site->id );
		Cookie::queue ( "userName", "Hoge1", 1 * 24 * 60 * 60 );
		return "Iko1";
	}
	public function getIko2(Site $site) {
		MySession::logout ( $site->id );
		Cookie::queue ( $site->id . "_userName", "Hoge2", 1 * 24 * 60 * 60 );
		return "Iko2";
	}
	public function getIko3(Site $site) {
		MySession::logout ( $site->id );
		Session::put ( $site->id . "_userName", "Hoge3" );
		return "Iko3";
	}
}