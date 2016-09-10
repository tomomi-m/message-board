<div name="popupLoginDiv" data-role="popup"
	class="popupLoginDiv ui-content ui-corner-all" data-history="false"
	style="width: 230px">
	<span style="margin: 0; font-weight: bold; text-align: center">ユーザー認証</span><span
		style="margin: 0; font-size: 6pt; font-weight: normal">毎回入力ではないのでご理解・ご協力ください。</span>
	<table>
		<tr>
			<td style="vertical-align: middle; width: 3.5em">お名前</td>
			<td><input style="" type="text" name="userNameInp" value=""
				data-mini="true" placeholder="お名前" data-theme="a" /></td>
		</tr>
	</table>
	<hr />
	<div name="captchaImgDiv"></div>
	<table>
		<tr>
			<td style="vertical-align: middle; width: 3.5em">上画像内文字</td>
			<td><input style="ime-mode: disabled" type="text" name="captchaInp"
				value="" data-mini="true" placeholder="↑画像内の文字" data-theme="a" /></td>
		</tr>
	</table>
	<button type="button" data-theme="b" data-mini="true"
		onclick="login(this,event)">認証</button>
</div>
<div name="popupLogoutDiv" class="ui-content ui-corner-all"
	data-role="popup" data-history="false">
	ログアウトします
	<div data-role="controlgroup" data-type="horizontal">
		<button type="button" data-mini="true" onclick="logout(this,event)">ログアウト</button>
		<button type="button" data-theme="b" data-mini="true"
			onclick="tom.$OP(this,'popupLogoutDiv').popup('close')">キャンセル</button>
	</div>
</div>
<div name="popupWelcomeDiv" class="ui-content ui-corner-all"
	data-role="popup" data-history="false">
	<h2>
		<span name="userNameSpn">?</span>さん。ようこそ！ <span name="siteNameSpn">?</span>
		サイトへ。
	</h2>
	<p>
		新規ユーザーを登録しました。<br> 画面右上の MENU から各種アクションを行えます。
	</p>
	<button type="button"
		onclick="tom.$OP(this, 'popupWelcomeDiv').popup('close')">OK</button>
</div>

