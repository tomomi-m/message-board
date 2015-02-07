@extends('site.page.base')
@section('header')
{{$site->header}}
@stop
@section('content')
{{$site->body}}
<div name="pageBodyDiv" class="body">
	<div name="topNavi" data-role="navbar" data-iconpos="left">
		<ul>
			<li><a href="#" onclick="topNaviShow('naviTopContentsDiv',this)" class="ui-btn-active naviSelected" data-icon="home">TOP</a></li>
			<li><a href="#" onclick="topNaviShow('naviLatestContentsDiv',this)" data-icon="star">最新</a></li>
			<li><a href="#" onclick="showSiteMap('naviSiteMpDiv',this)" data-icon="sitemap">マップ</a></li>
			<li><a href="#" onclick="topNaviShow('naviSearchDiv',this)" data-icon="spinner">[準備中]</a></li>
		</ul>
	</div>
	<div name="naviTopContentsDiv" class="topNavi">
		<div name="pageContentsDiv">
		{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->body)}}
		</div>
		<div style="clear:both"></div>
	@if(!$childPages->isEmpty())
		<ul data-role="listview" data-inset="true">
			@foreach ($childPages as $child)
			<li>
				<a href="{{{ $child->id }}}" data-pageId="{{{ $child->id }}}"><img src="{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $child->thumbnail)}}"/>{{{ $child->title }}}<br><div style="margin-left:1em; font-weight:normal; font-size: small;">{{MyDate::relativeDatetime($child->updated_at?$child->updated_at:$child->created_at)}} 更新<br/>{{$child->lastMessage_at?MyDate::relativeDatetime($child->lastMessage_at):"no"}} メッセージ</div></a>
			</li>
			@endforeach
		</ul>
		@endif
	</div>
	<div name="naviLatestContentsDiv" class="topNavi" style="display:none;">
		<div >最新更新ページへのショートカット</div>
		<div name="refreshLatestPagesAndMessagesDiv" style="background-color:white" data-role="navbar">
			<ul>
				<li><a href="#" onclick="refreshLatestPagesAndMessages(20)">最新20件</a></li>
				<li><a href="#" onclick="refreshLatestPagesAndMessages(40)">40件</a></li>
				<li><a href="#" onclick="refreshLatestPagesAndMessages(70)">70件</a></li>
				<li><a href="#" onclick="refreshLatestPagesAndMessages(100)">100件</a></li>
			</ul>
		</div>
		<div style="margin:0 20px 0 20px">
			<ul name="ulLatestPagesAndMessages" class="ulLatestPagesAndMessages" data-role="listview" data-inset="true">
			</ul>
		</div>
	</div>
	<div name="naviSiteMpDiv" class="topNavi siteMap" style="display:none;">
		<div style="height: 4em; padding-top:2em">
			now loading...
		</div>
	</div>
	<div name="naviSearchDiv" class="topNavi" style="display:none;">
		<div style="height: 4em; padding-top:2em">
			under construnction...
		</div>
	</div>
</div>
<hr/>
@stop

@section('footer')
<div class="grad"
	style="padding: 0.5em; margin-top: 3em; font-family: Verdana, Roboto, sans-serif;">
	<div style="font-size: 8pt; padding-right: 1em">
		android標準ブラウザで動きおかしい方は<a class="ui-link" href="market://details?id=org.mozilla.firefox"><img src="/image/site/firefox.png" alt="">FireFoxブラウザ</a>や<a class="ui-link" href="market://details?id=com.android.chrome"><img src="/image/site/chrome.png" alt="">Chromeブラウザ</a>でもお試しください。左記リンクからGooglePlayインストール画面に飛べます。<br>

		<a style="padding: 0; margin: 0" href="http://cool-liberty.com/"
			target="_blank"><img style="vertical-align: middle;"
			src="/image/site/chat/emotions/cool-liberty_06.gif" width="88"
			height="31" alt="ホームページ作成素材 Cool Liberty" /></a>ホームページ作成素材 Cool
		Liberty様のアイコンを使用しています <img style="vertical-align: middle;"
			src="/image/site/chat/emotions/unknown.gif"><br /> hosted by <a
			target="_blank" href="http://www.xserver.ne.jp/"><img
			style="vertical-align: middle;"
			src="http://www.xserver.ne.jp/common/img/header/logo.gif" width="80"
			height="15" /></a><br />
	</div>
	<div style="text-align: right; padding-right: 1em">supported by tomomi</div>
</div>
@stop

