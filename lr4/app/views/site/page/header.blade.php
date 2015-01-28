<div name="stdHeader" data-role="header">
	<h1 class="shadowImages" style="text-align:left; margin-left:4.3em; margin-right:6em; overflow:visible; white-space:normal"><img  style="float:left" src="{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->thumbnail)}}"/>{{{$page->title}}}</h1>
	@if ($isEditable) <?php ?>
	<p name="logedInUserNameP" class="ui-icon-user ui-btn-icon-left"
		style="display: none; position: absolute; left: 0; top: 0; font-weight: normal; font-size: x-small;"></p>
	<a onclick="openHeaderMenu(event)" data-icon="gear"
		class="ui-btn-right">MENU</a> @if(!empty($breadCrumb))
	<ul class="breadcrumbs">
		@foreach ($breadCrumb as $bread)
		<li><a href="{{$bread->id}}" data-role="none"><div>{{$bread->title}}</div></a></li>
		@endforeach
	</ul>
	@endif
	<div class="pageUpdatedAt" style="position:absolute; right:0; bottom:0;font-weight:normal;z-index:2">{{$page->updated_at?$page->updated_at->format('Y-m-d H:i').'更新':""}} {{$page->updated_by?('by '.$page->updated_by):''}}</div>
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