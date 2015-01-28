<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="content-language" content="ja">
<link rel="icon"
	href="{{{ Request::getBasePath() }}}/image/site/{{{ $site->id}}}/favicon.ico"
	type="image/vnd.microsoft.icon" />
<meta name="viewport" content="width=device-width,minimum-scale=1">
{{HTML::style('//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css')}}
{{HTML::style('/css/commadelimited-jQuery-Mobile-Icon/jqm-icon-pack-fa.css')}}
{{HTML::style('/css/site/page/page.css')}}
<style>
.bg {
	background: #ffffff url({{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $site->id, $site->background)}}) repeat;
}
</style>
{{HTML::script('//code.jquery.com/jquery-1.11.1.min.js')}}
<script>
// ダイアログ表示中にリロードされた場合に。
if( /^#(.*)&ui-state=dialog$/.test( location.hash ) ) {
	var topURL = location.href.split("#")[0];
	location.replace( topURL );
}

// ID指定でページ表示中（data-role="page"）にリロードされた場合にトップに戻す。
if( /^#(.*)$/.test( location.hash ) ) {
	var topURL = location.href.split("#")[0];
	location.replace( topURL );
}

$( document ).on( "mobileinit", function() {
  $.mobile.defaultPageTransition  = "none";
  $.mobile.selectmenu.prototype.options.nativeMenu = false;
});
$(document).on('click', 'a.anchor', function(e){
    e.preventDefault();
    var y = $($(this).attr('href')).offset().top;
    $('body, html').animate({ scrollTop: y-50 }, 500);
});
</script>
{{""/*HTML::script('//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js')*/}}
{{HTML::script('//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.js')}}
{{HTML::script('js/jquery.serializejson.min.js')}}
{{HTML::script('js/jquery-dateFormat.min.js')}}
{{HTML::script('js/jquery.lazyload.js')}}
{{HTML::script('/js/tomomi.js')}}
{{HTML::script('/js/site/page/page.js')}}
{{HTML::script('/js/wysiwyg-editor.js')}}
{{HTML::script('/js/swfobject.js')}}
</head>
<body>
@section('page')
<div data-role="page" name="pageDiv" data-back-btn-text="戻る" class="bg">
@show

@section('header')
	@include('site.page.header')
@show

@yield('content')
@show

@section('footer')
	@include('site.page.footer')
@show
@include('site.page.loginout')

	<div name="popupImageDiv" style="display:none" class="popupImageDiv"  data-overlay-theme="b" data-corners="false" data-tolerance="10,50,10,20">
		<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
		<div style="overflow:auto">
			<img />
		</div>
	</div>
	<div name="popupAlertDiv" data-role="popup" data-overlay-theme="b" class="ui-content ui-corner-all" data-history="false">
	</div>
	<div name="popupMovieDiv" style="display:none" class="popupMovieDiv"  data-overlay-theme="b" data-corners="false" data-tolerance="10,50,10,20">
		<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
		<div style="overflow:auto">
			<div name="movieDiv"></div>
		</div>
	</div>
</div>

<!--end of page-->
</body>
</html>
