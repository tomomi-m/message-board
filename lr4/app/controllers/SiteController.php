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
			Log::info ( "ログインエラー。userName:'" . $userName . "', captcha:'" . $data ['captcha'] . "'" );
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
		$response = Response::make ( "var myJsVersion='" . MyVersion::VER . "';" );
		$response->header ( 'Content-Type', "application/javascript" );
		return $response;
	}
	public function anySiteSearch(Site $site) {
		$data = Input::all ();
		if (! array_key_exists ( 'searchKeyword', $data )) {
			return "{'err': 'no keyword.'}";
		}
		$keyword = $data ['searchKeyword'];
		$keyword = trim ( str_replace ( "　", " ", $keyword ) );
		if (strlen ( $keyword ) == 0) {
			return "{'err': 'no keyword..'}";
		}
		$keywords = preg_split ( "/[\s]+/", $keyword );
		$queryForPage = DB::table ( 'pages' )->select ( DB::raw ( '\'1\' as type, id as pageId, null as messageId, title, body_for_search as message, updated_by as userName, null as imgFace, null as imgEmotion, updated_at' ) );
		$queryForPage->where ( 'site', $site->id );
		foreach ( $keywords as $queryKey ) {
			$queryForPage->where ( function ($query) use(&$queryKey) {
				$query->where ( 'title', 'like', "%{$queryKey}%" )->orWhere ( 'body_for_search', 'like', "%{$queryKey}%" );
			} );
		}
		$queryForMessage = DB::table ( 'messages' )->select ( DB::raw ( '\'2\', page, id, null, message, userName, imgFace, imgEmotion, updated_at' ) );
		$queryForMessage->where ( 'site', $site->id );
		foreach ( $keywords as $queryKey ) {
			$queryForMessage->where ( 'message', 'like', "%{$queryKey}%" );
		}
		$queryUnion = $queryForPage->union ( $queryForMessage );
		$results = $queryUnion->orderBy ( 'updated_at', 'desc' )->take ( self::SEARCH_PAGING )->get ();

		$retPageIds = array ();
		foreach ( $results as $result ) {
			$result->imgFace = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $result->imgFace );
			$result->imgEmotion = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $result->imgEmotion );
			$retPageIds [$result->pageId] = '1';
		}

		$ret ['searchResults'] = $results;

		$retPagesDef = array ();
		if (count ( $retPageIds ) > 0) {
			$pageIds = array_keys ( $retPageIds );
			$pages = Page::where ( 'site', $site->id )->whereIn ( 'id', $pageIds )->get ();
			foreach ( $pages as $page ) {
				$retPagesDef [] = array (
						'id' => $page->id,
						'thumb' => str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $page->site, $page->thumbnail ),
						'title' => $page->title
				);
			}
		}
		$ret ['hitPageInfo'] = $retPagesDef;

		if (count ( $results ) < self::SEARCH_PAGING) {
			$ret ['noMoreSearchResults'] = true;
		}

		$ret ['keywords'] = $keywords;
		return Response::json ( $ret );
	}
	public function anyMakeBodySearchTextAllPage(Site $site) {
		$isValidLogin = MySession::validateLogin ( $site->id );
		if ($isValidLogin)
			return $isValidLogin;
		$pages = Page::where ( 'site', $site->id )->get ();
		$updateCount = 0;
		foreach ( $pages as $page ) {
			SitePageController::makeBodySearchText ( $page );
			DB::update ( "update pages set body_for_search=? where site=? and id= ?", array (
					$page->body_for_search,
					$site->id,
					$page->id
			) );
			$updateCount ++;
		}
		return "Updated page: " . $updateCount;
	}
	public function anyGetFaces(Site $site) {
		$folders = (new SiteImage ())->getFaces ( $site );
		$ret = array ();
		if (! empty ( $folders )) {
			$ret = $folders [0] ["all"];
		}
		return Response::json ( $ret );
	}
}
