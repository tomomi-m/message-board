@extends('site.page.base')
@section('content')
<div name="pageBodyDiv" class="body">
<div name="pageContentsDiv">
{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->body)}}
</div>
<div style="clear:both">
@if(!$childPages->isEmpty())
	<ul data-role="listview" data-inset="true">
@foreach ($childPages as $child)
		<li>
			<a href="{{{ $child->id }}}"><img src="{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $child->thumbnail)}}"/>{{{ $child->title }}}<br><div style="margin-left:1em; font-weight:normal; font-size: small;">{{MyDate::relativeDatetime($child->updated_at?$child->updated_at:$child->created_at)}} 更新<br/>{{$child->lastMessage_at?MyDate::relativeDatetime($child->lastMessage_at):"no"}} メッセージ</div></a>
		</li>
@endforeach
	</ul>
@endif
</div>
@if ($page->hasChat == "Y")
	@include('site.page.chat')
@endif
</div>
@stop