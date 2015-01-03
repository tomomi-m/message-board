@extends('site.page.base')
<?php ?>
@section('header')
<div name="profileEditHeader" data-role="header" data-position="fixed"
	data-add-back-btn="true">
	<a href="." data-icon="arrow-l">戻る</a>
	<h3>個人設定</h3>
</div>
@stop
<?php ?>
@section('content')
<form name="editProfileForm"
	style="background: white; margin-bottom: 0px; margin-top: 0px"
	data-role="fieldcontain" class="ui-hide-label" data-type="horizontal"">
	<h3>アカウント</h3>
	<table>
		<cols>
		<col width="30%" />
		<col width="70%" />
		</cols>
		<tr>
			<td>User ID</td>
			<td>{{$user->id}} ※自動発番です</td>
		</tr>
		<tr>
			<td>お名前</td>
			<td><input type="text" name="profileUserNameInp" data-mini="true"
				data-role="fieldcontain" placeholder="Your Name"
				value="{{$user->userName}}" /></td>
		</tr>
		<div data-role="popup" name="userNameRequirePopup" class="ui-content"
			style="max-width: 350px; color: red; background-color: khaki; font-weight: bold" data-history="false">
			お名前を入力してください</div>
	</table>

	<h3>あなたのアイコン(メッセージ投稿時に使えます)</h3>
	<div name="profileEmotions" style="background: white">
		<fieldset style="margin: 0; position: relative;"
			data-role="controlgroup" data-type="horizontal" data-inline="true">
			<button style="width: 5em" tabindex="-1" type="button"
				data-icon="picture-o" data-iconpos="notext">&nbsp;</button>
			<input name="profileAttachImageInp" type="file" accept="image/*"
				style="display: none; position: absolute; top: 0; left: 0; width: 5em;"
				data-role="none" onchange="profileAppendImage(event, this)" />
			<div name="profileAttachImagesDiv" class="shadowImages"
				style="display: inline-block">
				@foreach ($userIconImgs as $key => $userIconImg)
				<table border="0" style="display: inline-block">
					<tr>
						<td><a name="moveImageBtn" @if ($key==0) style="display: none"
							@endif data-role="button" data-icon="arrow-left"
							onclick="moveImageInDiv(event)">&nbsp;</a></td>
					</tr>
					<tr>
						<td name="imageTd"><img src="{{$userIconImg}}" /></td>
					</tr>
					<tr>
						<td><a name="delImageBtn" data-role="button"
							onclick="deleteImageFromDiv(event)">削除</a></td>
					</tr>
				</table>
				@endforeach
			</div>
		</fieldset>
		<ul>
			<li>アイコンは複数登録できます。先頭画像がデフォルトとなります</li>
			<li>アイコンは最大サイズは48x48ピクセル四方です。大きい場合は自動縮小されます</li>
			<li>アイコンを更新した場合は、お手数ですが戻った先のページで再読込みを行ってアイコンリストを最新化してください。</li>
		</ul>
	</div>
<?php
/*
 * <h3>パスワード</h3> <table> <cols> <col width="30%" /> <col width="70%" /> </cols> <tr> <td>Password</td> <td><input type="password" name="profilePassword" data-mini="true" data-role="fieldcontain" placeholder="Password" /></td> </tr> <tr> <td>Password再</td> <td><input type="password" name="profilePasswordConfirm" data-mini="true" data-role="fieldcontain" placeholder="Password再" /></td> </tr> <tr> <td>パスワード忘れのときの秘密の質問</td> <td><input type="text" name="profileAltCertQ" data-mini="true" data-role="fieldcontain" placeholder="パスワード忘れのときの秘密の質問" /></td> </tr> <tr> <td>その答え</td> <td><input type="text" name="profileAltCertA" data-mini="true" data-role="fieldcontain" placeholder="その答え" /></td> </tr> </table>
 */
?>
	<div style="margin: 0 auto; width: 100%; text-align: center;">
		<input type="button" name="editProfileSaveBtn" data-icon="check"
			data-inline="true" value="{{$isNewProfile?"
			登録":"更新"}}" onclick="saveProfileEdit(this)" />
	</div>
</form>
@stop
