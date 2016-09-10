<?php
class MessagesController extends \BaseController {
	
	/**
	 * Display a listing of posts
	 *
	 * @return Response
	 */
	public function getIndex() {
		$posts = Message::all ();
		
		return View::make ( 'posts.index', compact ( 'posts' ) );
	}
	
	/**
	 * Show the form for creating a new post
	 *
	 * @return Response
	 */
	public function create() {
		return View::make ( 'posts.create' );
	}
	
	/**
	 * Store a newly created post in storage.
	 *
	 * @return Response
	 */
	public function postPostMessage() {
		$data = Input::except ( 'lastMessageId' );
		
		Message::create ( $data );
		return $this->anyGetLatestMessages ();
	}
	public function anyGetOlderMessages() {
		$data = Input::all ();
		
		$messages [] = array (
				'userName' => "ddd",
				'message' => "eee",
				'datetime' => date ( "Y-m-d H:i:s" ) 
		);
		return Response::json ( $messages );
	}
	public function anyGetLatestMessages() {
		$data = Input::all ();
		if ($data ['lastMessageId']) {
			$data ['lastMessageId'] = 0;
		}
		$messages = Message::where ( 'id', '>', $data ['lastMessageId'] )->orderBy ( 'id' )->get ();
		
		return Response::json ( $messages );
	}
	public function anyGetLatestXMessages() {
		$messages = Message::orderBy ( 'id', 'desc' )->take ( 5 )->get ();
		
		return Response::json ( array_reverse ( $messages->toArray () ) );
	}
	
	/**
	 * Display the specified post.
	 *
	 * @param int $id        	
	 * @return Response
	 */
	public function show($id) {
		$post = Message::findOrFail ( $id );
		
		return View::make ( 'posts.show', compact ( 'post' ) );
	}
	
	/**
	 * Show the form for editing the specified post.
	 *
	 * @param int $id        	
	 * @return Response
	 */
	public function edit($id) {
		$post = Message::find ( $id );
		
		return View::make ( 'posts.edit', compact ( 'post' ) );
	}
	
	/**
	 * Update the specified post in storage.
	 *
	 * @param int $id        	
	 * @return Response
	 */
	public function update($id) {
		$post = Message::findOrFail ( $id );
		
		$validator = Validator::make ( $data = Input::all (), Message::$rules );
		
		if ($validator->fails ()) {
			return Redirect::back ()->withErrors ( $validator )->withInput ();
		}
		
		$post->update ( $data );
		
		return Redirect::route ( 'posts.index' );
	}
	
	/**
	 * Remove the specified post from storage.
	 *
	 * @param int $id        	
	 * @return Response
	 */
	public function destroy($id) {
		Message::destroy ( $id );
		
		return Redirect::route ( 'posts.index' );
	}
}
