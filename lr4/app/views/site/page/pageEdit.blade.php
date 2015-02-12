@extends('site.page.base')
@section('header')
<div name="pageEditHeader" data-role="header" data-position="fixed" >
	<fieldset name="wysiwyg-toolbar" style="margin:0;position:relative;" data-role="controlgroup" data-type="horizontal" data-inline="true">
			<button style="width:5em;height:3.5em" class="wysiwyg-control" tabindex="-1" data-role="button"  data-icon="picture-o" data-iconpos="notext">&nbsp;</button>
			<input type="file" accept="image/*" class="wysiwyg-control" style="display:none;" data-wysiwyg-command="insertImage" data-role="none" multiple/>
			<button name="explode-toolbar-btn" style="width:2em;height:3.5em" class="wysiwyg-control small-only" data-wysiwyg-command="explode-toolbar" data-role="button" data-icon="angle-double-right" data-iconpos="notext">&nbsp;</button>
			<button name="implode-toolbar-btn" style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="implode-toolbar" data-role="button" data-icon="angle-double-left" data-iconpos="notext">&nbsp;</button>

			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="fontSize 1.5" data-role="button" data-icon="site-font-size-small" data-iconpos="notext">aaaaaaaaaaaa</button>
			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="fontSize 3" data-role="button" data-icon="site-font-size-normal" data-iconpos="notext">&nbsp;</button>
			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="fontSize 5" data-role="button" data-icon="site-font-size-large" data-iconpos="notext">&nbsp;</button>
			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="fontSize 6" data-role="button" data-icon="site-font-size-x-large" data-iconpos="notext">&nbsp;</button>

			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="bold" data-role="button" data-icon="bold" data-iconpos="notext">&nbsp;</button>
			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="underline" data-role="button" data-icon="underline" data-iconpos="notext">&nbsp;</button>

			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="foreColor black" data-role="button" data-icon="site-font-color-black" data-iconpos="notext">&nbsp;</button>
			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="foreColor red" data-role="button" data-icon="site-font-color-red" data-iconpos="notext">&nbsp;</button>

			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="indent" data-role="button" data-icon="indent" data-iconpos="notext">&nbsp;</button>
			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="outdent" data-role="debuttondent" data-icon="dedent" data-iconpos="notext">&nbsp;</button>

			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="justifyleft" data-role="button" data-icon="align-left" data-iconpos="notext">&nbsp;</button>
			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="justifycenter" data-role="button" data-icon="align-center" data-iconpos="notext">&nbsp;</button>
			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="justifyright" data-role="button" data-icon="align-right" data-iconpos="notext">&nbsp;</button>

			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="insertunorderedlist" data-role="button" data-icon="list-ul" data-iconpos="notext">&nbsp;</button>
			<button style="width:2em;height:3.5em" class="wysiwyg-control large-only" data-wysiwyg-command="insertorderedlist" data-role="button" data-icon="list-ol" data-iconpos="notext">&nbsp;</button>

			<button class="wysiwyg-control" data-wysiwyg-command="autoresize" data-role="button" >ﾘｻｲｽﾞ</button>
			<button class="wysiwyg-control" data-wysiwyg-command="autoresize2" data-role="button" >ﾘｻｲｽﾞ小</button>
		</fieldset>
</div>
@stop

@section('content')
<div name="pageEdit" data-isNewPage="{{$isNewPage}}" style="background: white">
	<div name="pageEditBodyDiv"
		style="background: white; padding: 1em; border: 2px solid;"
		contenteditable>{{ str_replace('${siteImage}',
		Request::getBasePath().'/image/site/'. $page->site, $page->body)}}
	</div>
	<br style="clear:both;">
	<span style="font-size:x-small">※PCブラウザのChromeとFireFoxであれば、上記枠内に画像をドラッグ＆ドロップ出来ます(IEでは1回しか何故か出来ない)<br/>
	※PCブラウザのIEとFireFoxであれば画像をクリックすると頂点が表示されて画像サイズを変更できます。スマホでは上記ボタンで画像サイズを調整するか、アップロード前に別ソフトで画像をリサイズしてください。</span>
	<div data-role="fieldcontain">
		タイトル<input type="text" name="titleInp" value="{{{$page->title}}}" /><br/>

		サムネイルアイコン
		<div name="thumbnail" >
			<img name="thumbnailImg" width="80" height="80" style="background: white; padding: 1em; border: 2px solid;" src="{{str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->thumbnail)}}"/>
			<fieldset style="display:inline-block" data-role="controlgroup" data-type="horizontal" data-inline="true">
				<button class="thumbnail-control" tabindex="-1" data-role="button"  data-icon="picture-o" data-iconpos="notext">&nbsp;</button>
				<input type="file" class="thumbnail-control" data-role="none"/>
			</fieldset>
		</div>
		<br/>
		チャット機能
		<select name="hasChatSelect" id="flip-mini" data-role="slider" data-mini="true">
			<option value="N">Off</option>
			<option value="Y" {{$page->hasChat?"selected":""}}>On</option>
		</select>
		<br/>
		<br/>
@if(isset($parentPage))
		親ページ <button style="width:20em;text-align:left" data-role="button" name="parentPageBtn" data-icon="gear" data-inline="true" data-mini="true" onclick="popupPageEditSelectParent(event)" data-parentId="{{$parentPage->id}}"><img width="40" src="{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $parentPage->thumbnail)}}"/>{{$parentPage->title}}</button>
@endif
	</div>
	<div style="margin: 0 auto; width: 100%; text-align: center;">
		<input type="button" name="pageEditSaveBtn" data-icon="check" data-inline="true" onclick="savePageEdit(this)" value="{{$isNewPage?"登録":"更新"}}" />
	</div>
</div>
<div name="popupParentPageSelectDiv" data-role="popup" data-history="false" data-tolerance="20">
<div  style="width:20em; height:20em; overflow: auto;">
	<ul name="parentPageSelectListUl" data-role="listview" style="margin:1em" data-inset="true" data-initialParent="{{$page->parent}}">
@if(isset($sitePages))
	@foreach ($sitePages as $sitePage)
		<li data-parentId="{{$sitePage->parent}}" data-pageId="{{$sitePage->id}}" ><a href="#" onclick="pageEditSelectParent(this)" style="padding:0"><span><img height="40" src="{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $sitePage->thumbnail)}}"/></span>{{$sitePage->title}}</a></li>
	@endforeach
@endif
	</ul>
</div>
</div>
@stop
