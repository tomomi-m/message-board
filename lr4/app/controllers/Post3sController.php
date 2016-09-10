<?php
class Post3sController extends \BaseController {
	
	/**
	 * Display a listing of post3s
	 *
	 * @return Response
	 */
	public function index() {
		$post3s = Post3::all ();
		
		return View::make ( 'post3s.index', compact ( 'post3s' ) );
	}
	
	/**
	 * Show the form for creating a new post3
	 *
	 * @return Response
	 */
	public function create() {
		return View::make ( 'post3s.create' );
	}
	
	/**
	 * Store a newly created post3 in storage.
	 *
	 * @return Response
	 */
	public function store() {
		$validator = Validator::make ( $data = Input::all (), Post3::$rules );
		
		if ($validator->fails ()) {
			return Redirect::back ()->withErrors ( $validator )->withInput ();
		}
		
		Post3::create ( $data );
		
		return Redirect::route ( 'post3s.index' );
	}
	
	/**
	 * Display the specified post3.
	 *
	 * @param int $id        	
	 * @return Response
	 */
	public function show($id) {
		$post3 = Post3::findOrFail ( $id );
		
		return View::make ( 'post3s.show', compact ( 'post3' ) );
	}
	
	/**
	 * Show the form for editing the specified post3.
	 *
	 * @param int $id        	
	 * @return Response
	 */
	public function edit($id) {
		$post3 = Post3::find ( $id );
		
		return View::make ( 'post3s.edit', compact ( 'post3' ) );
	}
	
	/**
	 * Update the specified post3 in storage.
	 *
	 * @param int $id        	
	 * @return Response
	 */
	public function update($id) {
		$post3 = Post3::findOrFail ( $id );
		
		$validator = Validator::make ( $data = Input::all (), Post3::$rules );
		
		if ($validator->fails ()) {
			return Redirect::back ()->withErrors ( $validator )->withInput ();
		}
		
		$post3->update ( $data );
		
		return Redirect::route ( 'post3s.index' );
	}
	
	/**
	 * Remove the specified post3 from storage.
	 *
	 * @param int $id        	
	 * @return Response
	 */
	public function destroy($id) {
		Post3::destroy ( $id );
		
		return Redirect::route ( 'post3s.index' );
	}
}
