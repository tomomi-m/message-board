<?php
class SitePageController extends BaseController {
	const LAST_X_MESSAGE_LENGTH = 20;
	const AROUND_AT_MESSAGE_LENGTH = 10;
	const PAGING_LENGTH = 50;
	const LATEST_UPDATE_TOP_NUM = 5;
	const IMAGE_EMOTIONS = "image/site/chat/emotions/";
	public function getIndex(Site $site, $pageIndex = 'home') {
		$data = Input::all ();
		$aroundAtMode = false;
		if ( array_key_exists ( 'around', $data )) {
			$aroundAtMode = true;
		}
		$pageWhere = Page::where ( 'site', $site->id );
		if ($pageIndex == "index")
			$pageIndex = "home";
		if ($pageIndex == "home") {
			$pageWhere = $pageWhere->where ( 'isDefault', 'Y' );
		} else {
			$pageWhere = $pageWhere->where ( 'id', $pageIndex );
		}
		$page = $pageWhere->first ();
		$childPages = null;
		$breadCrumb = null;
		if (!$aroundAtMode) {
			$childPages = Page::where ( 'site', $site->id )->where ( 'parent', $page->id )->orderBy ( 'title' )->orderBy ( 'id' )->get ();
		}
		$breadCrumb = $this->getBreadCrumbList ( $page );

		$userName = MySession::getUserName ( $site->id );

		return View::make ( 'site.page.' . (($pageIndex == "home") ? "home" : "page"), array (
				'site' => $site,
				'page' => $page,
				'childPages' => $childPages,
				'faces' => $this->getFaces ( $site ),
				'emotions' => $this->getEmotionCatalog ( $site ),
				'userName' => $userName,
				'isEditable' => ($pageIndex == "home") ? false : true,
				'breadCrumb' => $breadCrumb,
				'version' => MyVersion::VER,
				'aroundAtMode' => $aroundAtMode
		) );
	}
	public function anyGetAllPages(Site $site, $pageIndex) {
		$allPages = Page::where ( 'site', $site->id )->orderBy ( 'title' )->orderBy ( 'id' )->get ();
		$allPagesDef = array();
		foreach ($allPages as $page) {
			$allPagesDef[] = array(
				'id' => $page->id,
				'thumb' => str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->thumbnail),
				'title' => $page->title,
				'updatedBy' => $page->updated_by,
				'updatedAt' => MyDate::relativeDatetime($page->updated_at?$page->updated_at:$page->created_at),
				'lastMessageAt' => $page->lastMessage_at?MyDate::relativeDatetime($page->lastMessage_at):'no',
				'parent' => $page->parent?$page->parent:0,
				'isDefault' => $page->isDefault
			);
		}
		$ret ['allPages'] = $allPagesDef;
		return Response::json ( $ret );
	}
	function getBreadCrumbList($page) {
		$ret = array ();
		while ( $page->parent ) {
			$page = Page::where ( 'site', $page->site )->where ( 'id', $page->parent )->first ();
			if ($page->isDefault)
				$page->id = "home";
			$ret [] = $page;
		}

		return array_reverse ( $ret );
	}
	public function anyGetLatestXMessages(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );
		$messages = Message::where ( 'site', $site->id )->where ( 'page', $pageIndex )->orderBy ( 'id', 'desc' )->take ( self::LAST_X_MESSAGE_LENGTH )->get ();
		foreach ( $messages as $message ) {
			$message->images = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->images );
			$message->files = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->files );
		}
		$ret ['messages'] = array_reverse ( $messages->toArray () );
		if (count ( $ret ['messages'] ) < self::LAST_X_MESSAGE_LENGTH) {
			$ret ['noMoreMessages'] = true;
		}

		$ret ['authedUserName'] = MySession::getUserName ( $site->id );

		return Response::json ( $ret );
	}
	public function anyGetMessagesAroundAt(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );
		$data = Input::all ();
		$aroundAt = $data['around'];
		$takeOlderCount = self::AROUND_AT_MESSAGE_LENGTH / 2 -1;
		$takeNewerCount = self::AROUND_AT_MESSAGE_LENGTH / 2;
		$messagesLower = array_reverse(Message::where ( 'site', $site->id )->where ( 'page', $pageIndex )->where( 'id', '<=', $aroundAt)->orderBy ( 'id', 'desc' )->take ( $takeOlderCount)->get () ->toArray());
		$messagesUpper = Message::where ( 'site', $site->id )->where ( 'page', $pageIndex )->where( 'id', '>', $aroundAt)->orderBy ( 'id', 'asc' )->take ( $takeNewerCount)->get () ->toArray();
		$messages = array_merge($messagesLower, $messagesUpper);
		foreach ( $messages as &$message ) {
			$message["images"] = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message["images"] );
			$message["files"] = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message["files"] );
		}
		$ret ['messages'] = $messages;

		$ret ['authedUserName'] = MySession::getUserName ( $site->id );

			if (count ( $messagesLower ) < $takeOlderCount) {
			$ret ['noMoreOlderMessages'] = true;
		}
		if (count ( $messagesUpper) < $takeNewerCount) {
			$ret ['noMoreNewerMessages'] = true;
		}
		return Response::json ( $ret );
	}
	public function postAddMessage(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );

		$isHumanResponse = $this->validateHuman ( $site );
		if ($isHumanResponse)
			return $isHumanResponse;

		$data = Input::except ( 'lastMessageId' );

		$message = new Message ();
		$message->site = $site->id;
		$message->page = $pageIndex;
		// $message->userName = trim ( htmlspecialchars ( $data ['userName'], ENT_COMPAT, 'UTF-8' ) );
		$message->userName = MySession::getUserName ( $site->id );
		$message->userId = MySession::getUserId ( $site->id );
		if (array_key_exists ( 'title', $data )) {
			$message->title = htmlspecialchars ( $data ['title'], ENT_COMPAT, 'UTF-8' );
		}
		if (array_key_exists ( 'imgEmotion', $data )) {
			$message->imgEmotion = $data ['imgEmotion'];
		}
		if (array_key_exists ( 'imgFace', $data )) {
			$message->imgFace = $data ['imgFace'];
		}
		$message->message = nl2br ( htmlspecialchars ( $data ['message'], ENT_COMPAT, 'UTF-8' ) );
		if ($data ['attachImages']) {
			$message->images = $this->parseMessageAttatchImages ( $site->id, $data ['attachImages'] );
		}
		if (array_key_exists ( 'attachFilesName', $data )) {
			$message->files = $this->parseMessageAttatchFiles ( $site->id, $data ['attachFilesName'], $data ['attachFilesContents'] );
		}
		$message->save ();
		DB::update ( 'update pages set lastMessage_at = current_timestamp where site=? and id= ?', array (
				$site->id,
				$pageIndex
		) );

		return $this->anyGetLatestMessages ( $site, $pageIndex );
	}
	function parseMessageAttatchImages($siteId, $attachImagesHtml) {
		$imageStr = "";
		for($i = 0; $i < strlen ( $attachImagesHtml );) {
			$st = stripos ( $attachImagesHtml, ' src="data:image/', $i );
			if ($st === false) {
				break;
			}
			$st += 5;
			$i = $st + 1;
			$st = stripos ( $attachImagesHtml, '"', $i );
			$imageDataStr = substr ( $attachImagesHtml, $i, $st - $i );
			$i = $st + 1;
			$imageFileAndTh = (new SiteImage ())->createImageFileAndThumbnailFileJpg ( $siteId, $imageDataStr );
			$imageStr .= '<img src="' . $imageFileAndTh ['th'] . '" data-base-image="' . $imageFileAndTh ['base'] . '" /> ';
		}
		$imageStr = str_replace ( ' src="' . '/image/site/' . $siteId . '/', ' src="${siteImage}/', $imageStr );
		return $imageStr;
	}
	function parseMessageAttatchFiles($siteId, $attachFilesName, $attachFilesContents) {
		$filesAnchStr= "";
		for($index = 0; $index < count( $attachFilesName ); $index++) {
			$contentsStr = $attachFilesContents[$index];
			$filename = $attachFilesName[$index];
			$fileext = preg_replace( '/^.*(\..*)$/', '$1', $filename);
			if (stripos ( $contentsStr, 'data:', 0 ) === false) {
				throw new Exception ( 'invalid argument:not start "data:"' );
			}
			$i = 0;
			$st = stripos ( $contentsStr, ';', $i );
			$mime_type = substr ( $contentsStr, $i + 5, $st - 5 - $i );
			$i = $st + 1;
			$st = stripos ( $contentsStr, ',', $i );
			$enc = substr ( $contentsStr, $i, $st - $i );
			$i = $st + 1;
			$st = strlen ( $contentsStr );
			$fileBinaryStr = substr ( $contentsStr, $i, $st - $i );
			$fileBinary = base64_decode ( $fileBinaryStr );

			$fileNewPath = (new SiteImage ())->getNewImagePath ( $siteId );
			$fileNewName = $fileNewPath ['imageFileNameNoExt'] . $fileext;
			$fileNewPathAbs = $fileNewPath ['imageDirAbs'] . "/" . $fileNewName;
			$fileNewPathRel = $fileNewPath ['imageDir'] . "/" . $fileNewName;
			$fp = fopen ( $fileNewPathAbs, 'w' );
			fwrite ( $fp, $fileBinary );
			fclose ( $fp );

			$filesAnchStr .= '<a class="attachFile" data-href="' . $fileNewPathRel. '" data-filename="' . $filename . '" /> ';
		}
		$filesAnchStr = str_replace ( ' src="' . '/image/site/' . $siteId . '/', ' src="${siteImage}/', $filesAnchStr );
		return $filesAnchStr;
	}
	public function anyGetLatestMessages(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );
		$data = Input::all ();
		if (! array_key_exists ( 'lastMessageId', $data )) {
			$data ['lastMessageId'] = 0;
		}
		$messages = Message::where ( 'site', $site->id )->where ( 'page', $pageIndex )->where ( 'id', '>', $data ['lastMessageId'] )->orderBy ( 'id' )->get ();
		foreach ( $messages as $message ) {
			$message->images = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->images );
			$message->files = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->files );
		}

		$ret ['messages'] = $messages->toArray ();
		$ret ['authedUserName'] = MySession::getUserName ( $site->id );
		return Response::json ( $ret );
	}
	public function anyGetOlderMessages(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );
		$data = Input::all ();
		if (! array_key_exists ( 'olderMessageId', $data )) {
			App::abort ( 404, "Invalid olderMessageId" );
		}
		$pagingLength = self::PAGING_LENGTH;
		if (array_key_exists ( 'boost', $data )) {
			$pagingLength *= $data ["boost"];
		}
		$messages = Message::where ( 'site', $site->id )->where ( 'page', $pageIndex )->where ( 'id', '<', $data ['olderMessageId'] )->orderBy ( 'id', 'desc' )->take ( $pagingLength )->get ();
		foreach ( $messages as $message ) {
			$message->images = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->images );
			$message->files = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->files);
		}
		$ret ['messages'] = array_reverse ( $messages->toArray () );
		if (count ( $ret ['messages'] ) < $pagingLength) {
			$ret ['noMoreOlderMessages'] = true;
		}
		$ret ['authedUserName'] = MySession::getUserName ( $site->id );
		return Response::json ( $ret );
	}
	public function anyGetNewerMessages(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );
		$data = Input::all ();
		if (! array_key_exists ( 'newerMessageId', $data )) {
			App::abort ( 404, "Invalid newerMessageId" );
		}
		$pagingLength = self::PAGING_LENGTH;
		if (array_key_exists ( 'boost', $data )) {
			$pagingLength *= $data ["boost"];
		}
		$messages = Message::where ( 'site', $site->id )->where ( 'page', $pageIndex )->where ( 'id', '>', $data ['newerMessageId'] )->orderBy ( 'id', 'asc' )->take ( $pagingLength )->get ();
		foreach ( $messages as $message ) {
			$message->images = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->images );
			$message->files = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->files);
		}
		$ret ['messages'] = $messages->toArray ();
		if (count ( $ret ['messages'] ) < $pagingLength) {
			$ret ['noMoreNewerMessages'] = true;
		}
		$ret ['authedUserName'] = MySession::getUserName ( $site->id );
		return Response::json ( $ret );
	}
	function getFaces(Site $site) {
		$folders = array ();
		$userIcons = SiteUserIcon::where ( 'site', $site->id )->where ( 'userId', MySession::getUserId ( $site->id ) )->orderby ( 'order' )->get ();
		$userIconImgs = array ();
		if (count ( $userIcons )) {
			foreach ( $userIcons as $userIcon ) {
				$userIconImgs [] = mb_substr ( str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $userIcon->icon ), 1 );
			}
			$folders [] = array (
					"name" => "個人設定",
					"top" => $userIconImgs [0],
					"all" => $userIconImgs
			);
		}
		return $folders;
	}
	function getEmotionCatalog(Site $site) {
		$folders = array ();
		$dir = public_path ( self::IMAGE_EMOTIONS );
		$files = scandir ( $dir );
		sort ( $files );
		foreach ( $files as $file ) {
			$folder = null;
			if ($file == "." || $file == "..")
				continue;
			if (is_dir ( $dir . $file )) {
				$images = scandir ( $dir . $file );
				sort ( $images );
				foreach ( $images as $image ) {
					if (preg_match ( "/(gif|jpg|png)$/", $image )) {
						if (! $folder) {
							$folder = array (
									"name" => $file,
									"top" => self::IMAGE_EMOTIONS . $file . "/" . $image,
									"all" => array ()
							);
						}
						$folder ["all"] [] = self::IMAGE_EMOTIONS . $file . "/" . $image;
					}
				}
			}
			if ($folder)
				$folders [] = $folder;
		}
		return $folders;
	}
	public function anyGetEmotions(Site $site, $pageIndex = null) {
		$data = Input::all ();
		if (! array_key_exists ( 'folder', $data )) {
			App::abort ( 404, "Invalid folder" );
		}

		$images = array ();
		$dir = public_path ( self::IMAGE_EMOTIONS . $data ["folder"] . "/" );
		$files = scandir ( $dir );
		sort ( $files );
		foreach ( $files as $file ) {
			if ($file == "." || $file == "..")
				continue;
			if (is_file ( $dir . $file )) {
				if (preg_match ( "/(gif|jpg|png)$/", $dir . $file )) {
					$images [] = self::IMAGE_EMOTIONS . $data ["folder"] . "/" . $file;
				}
			}
		}
		return Response::json ( $images );
	}
	public function anyEditPage(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );

		$isValidLogin = $this->validateLogin($site);
		if ($isValidLogin)
			return $isValidLogin;

		$pageWhere = Page::where ( 'site', $site->id );
		if ($pageIndex == "index" || $pageIndex == "home") {
			$pageWhere = $pageWhere->where ( 'isDefault', 'Y' );
		} else {
			$pageWhere = $pageWhere->where ( 'id', $pageIndex );
		}
		$page = $pageWhere->first ();
		$parentPage = ($page->parent) ? Page::where ( 'site', $site->id )->where ( 'id', $page->parent )->first () : null;
		$sitePages = Page::where ( 'site', $site->id )->where ( 'id', '<>', $page->id )->orderBy ( 'title' )->orderBy ( 'id' )->get ();
		$userName = MySession::getUserName ( $site->id );
		return View::make ( 'site.page.pageEdit', array (
				'site' => $site,
				'page' => $page,
				'userName' => $userName,
				'isEditable' => false,
				'isNewPage' => false,
				'sitePages' => $sitePages,
				'parentPage' => $parentPage,
				'version' => MyVersion::VER
		) );
	}
	public function anySavePageEdit(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );

		$isHumanResponse = $this->validateHuman ( $site );
		if ($isHumanResponse)
			return $isHumanResponse;

		$data = Input::except ( 'lastMessageId' );

		if ($data ['isNewPage']) {
			$page = new Page ();
			$page->site = $site->id;
			$page->parent = $pageIndex;
			$page->save ();
		} else {
			$page = Page::where ( 'site', $site->id )->where ( 'id', $pageIndex )->first ();
			if (! $page)
				App::abort ( 404, "Not exist #PageID" );
			//backup prev page data
			DB::insert('insert into pagebaks (id,site,title,background,thumbnail,body,hasChat,parent,isDefault,editAuth,lastMessage_at,created_at,updated_at,updated_by) select id,site,title,background,thumbnail,body,hasChat,parent,isDefault,editAuth,lastMessage_at,created_at,updated_at,updated_by from pages where site=? and id=?', array($site->id, $pageIndex));

			if (isset ( $data ['parent'] ))
				$page->parent = $data ['parent'];
		}
		$page->title = trim ( $data ['title'] );
		if (strlen ( $page->title ) == 0)
			$page->title = null;
		$page->body = trim ( $data ['body'] );
		if (strlen ( $page->body ) == 0 || $page->body == '<br>')
			$page->body = null;
		$page->thumbnail = $data ['thumbnail'];
		$page->hasChat = ($data ['hasChat'] == "Y") ? "Y" : null;
		try {
			$this->parsePageBodyImage ( $page );
			$this->parsePageThumbnailImage ( $page );
		} catch ( Exception $e ) {
			if ($data ['isNewPage']) {
				$page->delete ();
			}
			throw $e;
		}
		$userName = MySession::getUserName ( $site->id );
		$page->updated_by = $userName;
		$page->save ();
		return Response::json ( array (
				'body' => str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $page->site, $page->body ),
				'thumbnail' => str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $page->site, $page->thumbnail )
		) );
	}
	function parsePageBodyImage($page) {
		// if (!$body) return $body;
		$bodyStr = "";
		for($i = 0; $i < strlen ( $page->body );) {
			$st = stripos ( $page->body, ' src="data:image/', $i );
			if ($st === false) {
				$bodyStr .= substr ( $page->body, $i );
				break;
			}
			$st += 5;
			$bodyStr .= substr ( $page->body, $i, $st - $i );
			$i = $st + 1;
			$st = stripos ( $page->body, '"', $i );
			$imageDataStr = substr ( $page->body, $i, $st - $i );
			$i = $st + 1;
			$imageFile = (new SiteImage ())->createImageFile ( $page->site, $imageDataStr );
			$bodyStr .= $imageFile;
		}
		$bodyStr = str_replace ( ' src="' . '/image/site/' . $page->site . '/', ' src="${siteImage}/', $bodyStr );
		$page->body = $bodyStr;
	}
	function parsePageThumbnailImage($page) {
		$st = stripos ( $page->thumbnail, 'data:image/', 0 );
		if ($st === false) {
		} else {
			$page->thumbnail = (new SiteImage ())->createImageFileFixedSizeJpg ( $page->site, $page->thumbnail );
		}
		return str_replace ( '/image/site/' . $page->site . '/', '${siteImage}/', $page->thumbnail );
	}
	public function anyCreateChildPage(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );

		$isValidLogin = $this->validateLogin($site);
		if ($isValidLogin)
			return $isValidLogin;

		// $parent = Page::find ( $pageIndex );
		$page = new Page ();
		$page->site = $site->id;
		$page->title = "---ここにページタイトル---";
		$page->body = "---ここにページ本文---";
		$page->parent = $pageIndex;
		$userName = MySession::getUserName ( $site->id );
		return View::make ( 'site.page.pageEdit', array (
				'site' => $site,
				'page' => $page,
				'childPages' => null,
				'userName' => $userName,
				'isEditable' => false,
				'isNewPage' => true,
				'version' => MyVersion::VER
		) );
	}
	public function anyDeletePage(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );

		$isValidLogin = $this->validateLogin($site);
		if ($isValidLogin)
			return $isValidLogin;

		$userName = MySession::getUserName ( $site->id );

		$updateCount = DB::update ( "update pages set site=CONCAT(site,'-none'), updated_at=current_timestamp, updated_by=? where site=? and id= ?", array (
				$userName,
				$site->id,
				$pageIndex
		) );
		return ($updateCount?"Deleted page:":"Page not found:") . $pageIndex;
	}
	function getLatestUpdated($site, $topNum) {
		$recentUpdatedPagesAndMessages = DB::select ( //
		"select type, p.id, p.site, p.title, p.thumbnail, case type when 1 then m.userName else p.updated_by end updated_by, case type when 1 then m.message else p.message end message, case type when 1 then m.updated_at else p.updated_at end updated_at " . //
		"from ( " . //
		"select 1 type, id, site, title, thumbnail, null updated_by, null message, lastMessage_at updated_at from pages where site=? " . //
		"union all select 2 type, id, site, title, thumbnail, ifnull(updated_by,'') updated_by, '[ページ更新]' message, updated_at from pages where site=? " . //
		"order by updated_at desc limit ? " . //
		") p left outer join messages m on p.type=1 and m.id=(select max(mmax.id) from messages mmax where mmax.site=p.site and mmax.page=p.id group by page) " . //
		"" , array (
				$site->id,
				$site->id,
				$topNum
		) );
		return $recentUpdatedPagesAndMessages;
	}
	function anyGetLatestPagesAndMessages(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );
		$data = Input::all ();
		$latestUpdated = $this->getLatestUpdated ( $site, $data ["topN"] );
		foreach ( $latestUpdated as $message ) {
			$message->thumbnail = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $message->thumbnail );
			$message->updated_at = MyDate::relativeDatetime ( $message->updated_at );
		}
		return Response::json ( array (
				'latestUpdated' => $latestUpdated
		) );
	}
	public function anyEditProfile(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );

		$isValidLogin = $this->validateLogin($site);
		if ($isValidLogin)
			return $isValidLogin;

		$data = Input::all ();
		$pageWhere = Page::where ( 'site', $site->id );
		if ($pageIndex == "index" || $pageIndex == "home") {
			$pageWhere = $pageWhere->where ( 'isDefault', 'Y' );
		} else {
			$pageWhere = $pageWhere->where ( 'id', $pageIndex );
		}
		$page = $pageWhere->first ();
		$user = MySession::getUser ( $site->id );
		$userIcons = SiteUserIcon::where ( "userId", $user->id )->orderby ( "order" )->get ();
		$userIconImgs = array ();
		foreach ( $userIcons as $userIcon ) {
			$userIconImgs [] = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $userIcon->icon );
		}
		return View::make ( 'site.page.editProfile', array (
				'site' => $site,
				'page' => $page,
				'user' => $user,
				'userIconImgs' => $userIconImgs,
				'isEditable' => false,
				'isNewProfile' => false,
				'version' => MyVersion::VER
		) );
	}
	public function anySaveProfileEdit(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );

		$isHumanResponse = $this->validateHuman ( $site );
		if ($isHumanResponse)
			return $isHumanResponse;

		$data = Input::all ();
		$userName = trim ( htmlspecialchars ( $data ['userName'], ENT_COMPAT, 'UTF-8' ) );
		$userId = MySession::getUserId ( $site->id );
		$userCheck = SiteUser::where ( 'site', $site->id )->where ( 'userName', $userName )->where ( 'id', '<>', $userId )->first ();
		if ($userCheck) {
			return Response::json ( array (
					"errorMsg" => "そのユーザー名は誰かに使用されています"
			), 202 );
		}

		$attachImagesHtml = $data ['attachImagesHtml'];
		$imageSrcs = null;
		if ($attachImagesHtml) {
			$imageSrcs = $this->parseProfileAttatchImages ( $site->id, $attachImagesHtml );
		}

		$user = MySession::updateUserName ( $site->id, $userName );

		SiteUserIcon::where ( 'userId', $user->id )->delete ();
		if ($imageSrcs) {
			foreach ( $imageSrcs as $key => $imageSrc ) {
				$image = new SiteUserIcon ();
				$image->site = $site->id;
				$image->userId = $user->id;
				$image->order = $key;
				$image->icon = $imageSrc;
				$image->save ();
			}
			foreach ( $imageSrcs as $key => $imageSrc ) {
				$imageSrcs [$key] = str_replace ( '${siteImage}', Request::getBasePath () . '/image/site/' . $site->id, $imageSrc );
			}
		}
		return Response::json ( array (
				'imageSrcs' => $imageSrcs
		) );
	}
	function parseProfileAttatchImages($siteId, $attachImagesHtml) {
		$ret = array ();
		for($i = 0; $i < strlen ( $attachImagesHtml );) {
			$st = stripos ( $attachImagesHtml, ' src="', $i );
			if ($st === false) {
				break;
			}
			$st += 5;
			$i = $st + 1;
			$st = stripos ( $attachImagesHtml, '"', $i );
			$imageDataStr = substr ( $attachImagesHtml, $i, $st - $i );
			$i = $st + 1;
			if (mb_substr ( $imageDataStr, 0, 11 ) == "data:image/") {
				$imageFile = (new SiteImage ())->createImageFileMaxSizeFix ( $siteId, $imageDataStr );
			} else {
				$imageFile = $imageDataStr;
			}
			$ret [] = $imageFile;
		}
		return $ret;
	}
	function validateHuman(Site $site) {
		$isHuman = MySession::getUserId ( $site->id );
		if ($isHuman)
			return null;

		$response = Response::make ( '{"r":"are you human?"}', 202 );
		return $response;
	}
	function validateLogin(Site $site) {
		$isHuman = MySession::getUserId ( $site->id );
		if ($isHuman)
			return null;

		$response = Response::make ( 'Unauthorized', 403 );
		return $response;
	}

	public function anyDownloadAttachFile(Site $site, $pageIndex = null) {
		if (! $pageIndex)
			App::abort ( 404, "Invalid #Page" );
		$data = Input::all ();
		$filename = $data[ 'filename'];
		$filePathRel = $data[ 'href'];
		$filePathAbs = public_path ( Request::getBasePath () . $filePathRel );
		return Response::download($filePathAbs, $filename, array('content-type' => 'application/octet-stream'));
	}
}