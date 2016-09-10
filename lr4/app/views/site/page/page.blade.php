@extends('site.page.base') @section('content')
<div name="pageBodyDiv" class="body">
	@if ($page->hasChat == "Y")
	<div align="right">
		<a href="#pageContentBottom{{$page->id}}" name="goMessageAnchor"
			class="anchor"
			style="display: none; padding: 0.4em; padding-left: 2em;"
			data-role="button" data-inline="true" data-mini="true"
			data-icon="long-arrow-down">メッセージ域へジャンプ</a>
	</div>
	@endif
	<div name="pageContentsDiv">{{ str_replace('${siteImage}',
		Request::getBasePath().'/image/site/'. $page->site, $page->body)}}</div>
	<div style="clear: both">
		@if($childPages && !$childPages->isEmpty())
		<ul data-role="listview" data-inset="true">
			@foreach ($childPages as $child)
			<li><a href="{{{ $child->id }}}"><img
					src="{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $child->thumbnail)}}" />{{{
					$child->title }}}<br>
				<div
						style="margin-left: 1em; font-weight: normal; font-size: small;">
						{{MyDate::relativeDatetime($child->updated_at?$child->updated_at:$child->created_at)}}
						更新<br />{{$child->lastMessage_at?MyDate::relativeDatetime($child->lastMessage_at):"no"}}
						メッセージ
					</div></a></li> @endforeach
		</ul>
		@endif
	</div>
	@if ($page->hasChat == "Y") <a id="pageContentBottom{{$page->id}}"></a>
	@include('site.page.chat') @endif
</div>
@stop
