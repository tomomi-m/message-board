<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="content-language" content="ja">
<title>{{{$page->title}}}</title>
<link rel="icon"
	href="{{{ Request::getBasePath() }}}/image/site/{{{ $site->id}}}/favicon.ico"
	type="image/vnd.microsoft.icon" />
<meta name="viewport" content="width=device-width,minimum-scale=1">
{{HTML::style('/css/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.css')}}
{{HTML::style('/css/commadelimited-jQuery-Mobile-Icon/jqm-icon-pack-fa.css')}}
{{HTML::style('/css/site/page/page.css')}}
<style>
.bg {
	background: #ffffff url({{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $site->id, $site->background)}}) repeat;
}
</style>
{{HTML::script('/js/jquery-1.12.4.min.js')}}
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
{{HTML::script('/js/jquery.mobile-1.4.5.min.js')}}
{{HTML::script('/js/jquery.serializejson.min.js')}}
{{HTML::script('/js/jquery-dateFormat.min.js')}}
{{HTML::script('/js/jquery.lazyload.js')}}
{{HTML::script('/js/jquery.cookie.js')}}
{{HTML::script('/js/tomomi.js')}}
{{HTML::script('/js/site/page/page.js')}}
{{HTML::script('/js/wysiwyg-editor.js')}}
{{HTML::script('/js/swfobject.js')}}
{{HTML::script('/siteg/' . $site->id . '/version-js')}}
</head>
<body>
@section('page')
<div data-role="page" name="pageDiv" data-title="{{{$page->title}}}" data-back-btn-text="戻る" class="bg" data-version="{{$version}}" 
@if (!Request::secure())
	data-ssl-site="{{Config::get('tomomi.ssl_site_host')}}"
@endif
>
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

	<div name="scrollDiv" style="display:table; position:fixed; bottom:1.5em; right:0px; z-index:3; ">
		<div style="table-row; margin-bottom:0.3em">
			<div onclick="$('body, html').animate({ scrollTop: 0 }, 500);" style="display:table-cell;background-color:rgba(200, 200, 200, 0.6); width:3em; height: 2em;border-radius:10px;">
				<div style="margin: 0.2em; border-left: 1.4em solid transparent;border-bottom: 0.7em solid;border-right: 1.4em solid transparent; z-index:10; "></div>
			</div>
			<div style="display:table-cell;width:0.5em">
			</div>
			<div onclick="$('body, html').animate({ scrollTop: $(document).height()-$(window).height()  }, 500);" style="display:table-cell;background-color:rgba(200, 200, 200, 0.6); width:3em; height: 2em;border-radius:10px;vertical-align:bottom;">
				<div style="margin: 0.2em; border-left: 1.4em solid transparent;border-top: 0.7em solid;border-right: 1.4em solid transparent;"></div>
			</div>
		</div>
	</div>

	<div name="popupImageDiv" style="display:none" class="popupImageDiv"  data-overlay-theme="b" data-corners="false" data-tolerance="10,50,10,20">
		<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
		<div style="overflow:auto">
			<img />
		</div>
	</div>
	
	<div name="popupAlertDiv" data-role="popup" data-overlay-theme="b" class="ui-content ui-corner-all" data-history="false" data-tolerance="50">
	</div>
	<div name="popupMovieDiv" style="display:none" class="popupMovieDiv"  data-overlay-theme="b" data-corners="false" data-tolerance="10,50,10,20">
		<a href="#" data-rel="back" data-role="button" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
		<div style="overflow:auto">
			<div name="movieDiv"></div>
		</div>
	</div>
	
	<div name="popupConfirmDiv" class="ui-content ui-corner-all"
		data-role="popup" data-history="false" data-tolerance="50">
		<div name="message" style=""></div>
		<div data-role="controlgroup" data-type="horizontal">
			<button type="button" name="okButton" style="width:7em">OK</button>
			<button type="button" data-theme="b" data-mini="true"
				onclick="tom.$OP(this,'popupConfirmDiv').popup('close')">キャンセル</button>
		</div>
	</div>
</div>

<!--end of page-->
</body>
</html>
