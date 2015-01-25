<hr />
<div name="messageInputDiv" style="margin-bottom: 0px; margin-top: 0px"
	data-role="fieldcontain" class="ui-hide-label" data-type="horizontal">
	<table width="100%" border="0">
		<tr>
			<td width="50px" name="emotion">
@if(!empty($faces))
				<a onclick="openFaces(event)" data-role="button" data-mini="true" data-rel="popup" style="padding: 0; height:4em;">
					<img
						src="/{{$faces[0]['all'][0]}}"
						name="imgFace" />
				</a>
@endif
				<a onclick="openEmotions(event)" data-role="button" data-mini="true" data-rel="popup" style="padding: 0; height:3em;">
					<img name="imgEmotion"
@if(empty($faces))
						src="/image/site/chat/emotions/unknown.gif"
@endif
						/>
				</a>
			</td>
			<td >
				<textarea name="messageTxt" placeholder="内容" data-mini="true" data-inline="true" contenteditable></textarea>
				<a name="chatExpandInputDetailBtn" onclick="chatExpandInputDetail(this)"
					style="width: 3em; height: 2em; display: none" data-role="button" data-icon="plus" data-iconpos="notext"></a>
				<a name="chatContractInputDetailBtn" onclick="chatContractInputDetail(this)"
					style="width: 3em; height: 2em" data-role="button" data-icon="minus" data-iconpos="notext"></a>
			</td>
			<td width="50px"><input type="button" name="messageSubmitBtn" data-icon="site-ok-submit" data-inline="true" data-mini="true" data-iconpos="bottom" disabled onclick="postMessage(this)" value="送">
			</td>
		</tr>
		<tr name="chatInputDetailSection">
			<td colspan="3">
				<fieldset style="margin: 0; position: relative;"
					data-role="controlgroup" data-type="horizontal" data-inline="true">
					<button style="width: 5em" tabindex="-1" type="button"
						data-icon="picture-o" data-iconpos="notext">&nbsp;</button>
					<input name="chatAttachImageInp" type="file" accept="image/*"
						style="display: none; position: absolute; top: 0; left: 0; width: 5em;"
						data-role="none" onchange="chatAppendImage(event, this)"/>
					<div name="chatAttachImagesDiv" class="shadowImages"
						style="display: inline-block"></div>
				</fieldset>
			</td>
		</tr>
	</table>
</div>
<div data-role="popup" name="facesPopupDiv" data-history="false" data-tolerance="20">
	<div style="margin: 0; width: 230px;">
	<div style="margin:0" onclick="setFace(event);"><img style="padding-left:8px; padding-right:8px" data-no-settings="Y" data-original="/{{SitePageController::IMAGE_EMOTIONS}}noSetting.gif" width="32" height="32" class="lazy" /></div>
	<hr style="margin:2px"/>
	@if (!empty($faces))
	@foreach ($faces[0]["all"] as $image)
		<span
			onclick="setFace(event);"><img data-original="/{{$image}}"
			width="48" height="48" class="lazy" /></span>
	@endforeach
@endif
	</div>
</div>
<div data-role="popup" name="emotionsPopupDiv" data-history="false" data-tolerance="20" style="width:257px">
	<div data-role="collapsible-set" data-collapsed-icon="arrow-r"
		data-expanded-icon="arrow-d" style="margin: 0;">
	<div style="margin:0" onclick="setEmotion(event);"><img style="padding-left:8px; padding-right:8px" data-no-settings="Y" data-original="/{{SitePageController::IMAGE_EMOTIONS}}noSetting.gif" width="32" height="32" class="lazy" /></div>
@foreach ($emotions as $key => $folder)
		<div data-role="collapsible">
			<h3 class="emotions">
				{{{ $folder["name"] }}} <img data-original="/{{$folder["top"]}}" width="32" height="32" class="lazy" />...他
			</h3>
@foreach ($folder["all"] as $image)
			 <span
				onclick="setEmotion(event);"><img data-original="/{{$image}}"
				width="40" height="40" class="lazy" /></span>
@endforeach
		</div>
@endforeach
	</div>
</div>
<ul name="messagesUl" class="messageUL" style="position:relative;min-height:25px" data-role="listview"
	data-inset="true">
	<div name="messagePollProgressDiv" style="position:absolute; right:0; top: 0; color:gray;">??</div>
	<li>loading...</li>

</ul>
<div data-role="navbar" style="margin-right:6px">
	<ul>
		<li><a data-role="button" name="messageGetOlderA"
			style="padding: 0; display: none" onclick="getOlderMessage(this)"><img
				width="26" height="26" src="/image/site/downarrow_midori.png" /></a></li>
		<li><a data-role="button" data-get-boost="2" name="messageGetOlderA"
			style="padding: 0; display: none" onclick="getOlderMessage(this)"><img
				width="26" height="26" src="/image/site/downarrow_midori.png" />x2</a></li>
		<li><a data-role="button" data-get-boost="3" name="messageGetOlderA"
			style="padding: 0; display: none" onclick="getOlderMessage(this)"><img
				width="26" height="26" src="/image/site/downarrow_midori.png" />x3</a></li>
	</ul>
</div>
