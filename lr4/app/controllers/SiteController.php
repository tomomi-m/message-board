<?php
class SiteController extends BaseController {
	const SEARCH_PAGING = 100;

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

	public function getVersionJs(Site $site, $pageIndex = null) {
		$response = Response::make ("var myJsVersion='" . MyVersion::VER . "';");
		$response->header('Content-Type', "application/javascript");
		return $response;
	}


	public function anySiteSearch(Site $site) {
		$data = Input::all ();
		if (!array_key_exists('searchKeyword', $data)) {
			return "{'err': 'no keyword.'}";
		}
		$keyword = $data['searchKeyword'];
		$keyword = trim(str_replace("　", " ",$keyword));
		if (strlen($keyword) == 0) {
			return "{'err': 'no keyword..'}";
		}
		$keywords = preg_split("/[\s]+/", $keyword);
		$query = Message::where ( 'site', $site->id );
		foreach ( $keywords as $queryKey ) {
			$query ->where ( 'message', 'like', "%{$queryKey}%");
		}
		$messages = $query ->orderBy ( 'updated_at', 'desc' )->take (self::SEARCH_PAGING )->get ();
		$retPageIds = array();
		foreach ( $messages as $message ) {
			$message->images = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->images );
			$message->files = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->files );
			$retPageIds[ $message->page ] = '1';
		}
		$ret ['messages'] = $messages->toArray ();

		$retPagesDef = array();
		if (count($retPageIds) > 0) {
			$pageIds = array_keys( $retPageIds );
			$pages = Page::where ( 'site', $site->id )->whereIn( 'id', $pageIds)->get ();
			foreach ($pages as $page) {
				$retPagesDef[] = array(
					'id' => $page->id,
					'thumb' => str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->thumbnail),
					'title' => $page->title,
				);
			}
		}
		$ret ['hitPageInfo'] = $retPagesDef;

		if (count ( $ret ['messages'] ) < self::SEARCH_PAGING) {
			$ret ['noMoreMessages'] = true;
		}

		$ret[ 'keywords' ] = $keywords;
		return Response::json ( $ret );
	}
}
