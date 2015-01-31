<div name="stdHeader" data-role="header">
	<div class="table" style="width:100%">
		<div class="row">
			<div style="padding: 0px; width:1px;">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw%3D%3D" style="height:4em; width:1px;"/>
			</div>
			<div style="padding: 0px; width:50px;">
			<img style="border-radius: 5px" height="50px" src="{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->thumbnail)}}" align="top"/>
			</div>
			<div style="overflow:visible; white-space:normal; word-break: break-all; padding: 0em 0.5em 0em 0em;">
				{{{$page->title}}}
			</div>
			<div style="width:1em; padding:0px;">
@if ($isEditable) <?php ?>
				<a onclick="openHeaderMenu(event)" data-icon="gear" class="ui-btn-right" data-role="button" style="position:relative">MENU</a>
@endif
				<div style="font-weight: normal; font-size: x-small; width: 8em; margin-top:1em; word-wrap: break-word;min-height:2em"><span name="logedInUserNameP" class="ui-icon-user ui-btn-icon-left" style="position:relative; margin-left:-0.5em"></span></div>
			</div>
		</div>
	</div>

	 @if(!empty($breadCrumb))
	<ul class="breadcrumbs">
		@foreach ($breadCrumb as $bread)
		<li><a href="{{$bread->id}}" data-role="none"><div>{{$bread->title}}</div></a></li>
		@endforeach
	</ul>
	@endif

	<div class="pageUpdatedAt" style="position:absolute; right:0; bottom:0;font-weight:normal;z-index:2">{{$page->updated_at?$page->updated_at->format('Y-m-d H:i').'更新':""}} {{$page->updated_by?('by '.$page->updated_by):''}}</div>

	@if ($isEditable) <?php ?>
	<div name="popupMenu" class="query-loged-in" data-role="popup"
		data-history="false" data-tolerance="10,30">
		<ul data-role="listview" data-inset="true" style="min-width: 210px;"
			data-theme="b">
			<li style="padding-top: 0; padding-bottom: 0" data-role="divider"
				data-theme="a">User Management</li>
			<li class="need-query-loged-in" style="font-size: x-small">ログイン状況問い合わせ中...</li>
			<li class="need-not-loged-in" data-icon=user><a
				onclick="$(this).closest('[name=\'popupMenu\']').popup('close');loginPopup(this);">ログイン</a></li>
			<li class="need-not-loged-in" style="font-size: x-small">ログインすると他メニュー有</li>
			<li class="need-loged-in" data-icon=user><a
				onclick="event.preventDefault();tom.$OP(this, 'popupMenu').popup('close');tom.$OPC(this, 'pageDiv', 'popupLogoutDiv').popup('open');">ログアウト</a></li>
			<li class="need-loged-in" data-icon=user><a
				onclick='event.preventDefault();$.mobile.changePage("{{ $page->id}}/edit-profile",{reloadPage :true });'>個人設定</a></li>
			<li class="need-loged-in" style="padding-top: 0; padding-bottom: 0"
				data-role="divider" data-theme="a">Page Management</li>
			<li class="need-loged-in" data-icon="edit"><a
				onclick='event.preventDefault();$.mobile.changePage("{{ $page->id}}/edit-page");'>ページ編集</a></li>
			<li class="need-loged-in" data-icon=file-text><a
				onclick='event.preventDefault();$.mobile.changePage("{{ $page->id}}/create-child-page",{reloadPage :true });'>新規に子ページ作成</a></li>
		</ul>
	</div>
	@endif
</div>