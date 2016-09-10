<hr />
<div name="messageInputDiv" style="margin-bottom: 0px; margin-top: 0px"
	data-role="fieldcontain" class="ui-hide-label" data-type="horizontal">
	<table width="100%" border="0">
		<tr>
			<td width="50px" name="emotion">
				<a name="faceSel" onclick="openFaces(event)" data-role="button" data-mini="true" data-rel="popup" style="padding: 0; height:4em;
				display:none;
				">
					<img
						name="imgFace" />
				</a>
				<a name="emotionSel" onclick="openEmotions(event)" data-role="button" data-mini="true" data-rel="popup" style="padding: 0; height:3em;">
					<img name="imgEmotion"
						src="/image/site/chat/emotions/unknown.gif"
						data-noFaces-default="/image/site/chat/emotions/unknown.gif"
						/>
				</a>
			</td>
			<td>
				<textarea name="messageTxt" placeholder="内容" style="margin-bottom: 0px" data-mini="true" data-inline="true" contenteditable></textarea>
				<fieldset style="margin-left:2em; position: relative; display:inline-block" data-role="controlgroup" data-type="horizontal" data-inline="true">
					<button style="width: 8em; height: 2.3em; padding:0; line-height:0.7em" tabindex="-1" type="button"
						data-icon="file"><span style="font-size: 60%; font-weight: normal">png, jpg, gif<br/>, psd, ai, pdf</span></button>
					<input name="chatAttachImageInp" type="file" accept="image/*,.psd,.ai,.pdf"
						style="display: none; position: absolute; top: 0; left: 0; width: 8em;"
						data-role="none" onchange="chatAppendImage(event, this)" multiple/>
				</fieldset>
				</td>
			<td width="50px"><input type="button" name="messageSubmitBtn" data-icon="site-ok-submit" data-inline="true" data-mini="true" data-iconpos="bottom" disabled onclick="postMessage(this)" value="送">
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<div name="chatAttachImagesDiv" class="shadowImages"></div>
				<div name="chatAttachFilesDiv"></div>
			</td>
		</tr>
	</table>
</div>
<div data-role="popup" name="facesPopupDiv" data-history="false" data-tolerance="20">
	<div style="margin: 0; width: 230px;">
	<div style="margin:0" onclick="setFace(event);"><img style="padding-left:8px; padding-right:8px" data-no-settings="Y" data-original="/{{SiteImage::IMAGE_EMOTIONS}}noSetting.gif" width="32" height="32" class="lazy" /></div>
	<hr style="margin:2px"/>
	<div name="faces">
		<span
			onclick="setFace(event);" style="display:none" data-template="yes"><img data-original=""
			width="48" height="48" class="lazy"/></span>
	</div>
	</div>
</div>
<div data-role="popup" name="emotionsPopupDiv" data-history="false" data-tolerance="20" style="width:257px">
	<div data-role="collapsible-set" data-collapsed-icon="arrow-r"
		data-expanded-icon="arrow-d" style="margin: 0;">
	<div style="margin:0" onclick="setEmotion(event);"><img style="padding-left:8px; padding-right:8px" data-no-settings="Y" data-original="/{{SiteImage::IMAGE_EMOTIONS}}noSetting.gif" width="32" height="32" class="lazy" /></div>
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
<div name="messageNavibarUp" data-role="navbar" style="margin-right:6px; ">
	<ul>
		<li><a data-role="button" name="messageGetNewerA"
			style="padding: 0; display: none" onclick="getNewerMessage(this)"><img
				width="26" height="26" src="/image/site/uparrow_midori.png" /></a></li>
		<li><a data-role="button" data-get-boost="2" name="messageGetNewerA"
			style="padding: 0; display: none" onclick="getNewerMessage(this)"><img
				width="26" height="26" src="/image/site/uparrow_midori.png" />x2</a></li>
		<li><a data-role="button" data-get-boost="3" name="messageGetNewerA"
			style="padding: 0; display: none" onclick="getNewerMessage(this)"><img
				width="26" height="26" src="/image/site/uparrow_midori.png" />x3</a></li>
	</ul>
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
