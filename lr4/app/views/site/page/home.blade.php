@extends('site.page.base')
@section('header')
{{$site->header}}
@stop
@section('content')
{{$site->body}}
<div name="pageBodyDiv" class="body">
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
<hr/>
<span>更新ページへのショートカット</span>
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
<hr/>
<button name="showSiteMapBtn" data-icon="chevron-down" data-mini="true" onclick="showSiteMap()">サイトマップを表示</button>
<button name="hideSiteMapBtn" data-icon="chevron-up" data-mini="true" onclick="hideSiteMap()" style="display: none" >サイトマップを非表示</button>
<div name="siteMapDiv" class="siteMap">
	@if(!$allPages->isEmpty())
	<script>
		var allPagesDef = [
		@foreach ($allPages as $page)
			{ id: {{{$page->id }}}, thumb: "{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->thumbnail)}}", title: "{{ MyStr::jsionEscape($page->title) }}", updatedBy: "{{ MyStr::jsionEscape($page->updated_by) }}", updatedAt: "{{MyDate::relativeDatetime($page->updated_at?$page->updated_at:$page->created_at)}}", lastMessageAt: "{{$page->lastMessage_at?MyDate::relativeDatetime($page->lastMessage_at):'no'}}", parent: {{{$page->parent?$page->parent:0}}}, isDefault: "{{$page->isDefault}}" },
		@endforeach
		];
	</script>
	@endif
</div>
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

