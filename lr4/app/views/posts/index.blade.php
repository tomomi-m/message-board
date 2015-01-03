@extends('posts.base')
@section('content')

<script>
$(document).on("submit", "#myForm", function(e) {
		e.preventDefault();

		var $form = $(this);
		var $button = $form.find('button');
		var postData = $form.serializeJSON();
		var lastMessageLi = $("#messages li:last");
		if (lastMessageLi.length > 0) {
			postData["lastMessageId"] = JSON.parse(lastMessageLi.attr("user-data")).id ;
		}
		$.ajax({
			url: $form.attr('action'),
			type: $form.attr('method'),
			dataType: 'json',
			data: postData,
			timeout: 10000,  // 単位はミリ秒
			beforeSend: function(xhr, settings) {
				$button.attr('disabled', true);
			},
			complete: function(xhr, textStatus) {
				$button.attr('disabled', false);
			},
			success: function(result, textStatus, xhr) {
				$form[0].reset();
				$.each(result, function(i, val) {
					var li = $("<li>"+val.created_at+" "+val.userName+": "+val.message+"</li>");
					li.attr("user-data",JSON.stringify({id: val.id}));
					$('#messages').append(li);
				});
				$(messages).listview('refresh');
			},
			error: function(xhr, textStatus, error) {
				alert("error!: "+textStatus);
			}
		});
	});

$(function(){
    $(downarrow).on('click',function(event){
        event.preventDefault();
        event.stopPropagation();
		var $button = $(document).find('button');
		var postData = {};
		var lastMessageLi = $("#messages li:last");
		if (lastMessageLi.length > 0) {
			postData["lastMessageId"] = JSON.parse(lastMessageLi.attr("user-data")).id ;
		}

        $.ajax({
    		url: "posts/get-latest-messages",
    		type: "POST",
    		dataType: 'json',
    		data: postData,
    		timeout: 10000,  // 単位はミリ秒
    		beforeSend: function(xhr, settings) {
    			$button.attr('disabled', true);
    		},
    		complete: function(xhr, textStatus) {
    			$button.attr('disabled', false);
    		},
    		success: function(result, textStatus, xhr) {
    			$.each(result, function(i, val) {
    				var li = $("<li>"+val.created_at+" "+val.userName+": "+val.message+"</li>");
    				li.attr("user-data",JSON.stringify({id: val.id}));
    				$('#messages').append(li);
    			});
    			$(messages).listview('refresh');
    		},
    		error: function(xhr, textStatus, error) {
    			alert("error!: "+textStatus);
    		}
    	});
        return false;
    });
    $(uparrow).on('click',function(event){
        event.preventDefault();
        event.stopPropagation();
		var $button = $(document).find('button');
        $.ajax({
    		url: "posts/get-older-messages",
    		type: "POST",
    		dataType: 'json',
    		data: {
    			lastMessageId: 1,
    			},
    		timeout: 10000,  // 単位はミリ秒
    		beforeSend: function(xhr, settings) {
    			$button.attr('disabled', true);
    		},
    		complete: function(xhr, textStatus) {
    			$button.attr('disabled', false);
    		},
    		success: function(result, textStatus, xhr) {
    			$.each(result, function(i, val) {
    				var li = $("<li>"+val.created_at+" "+val.userName+": "+val.message+"</li>");
    				li.attr("user-data",JSON.stringify({id: val.id}));
    				$('#messages').prepend(li);
    			});
    			$(messages).listview('refresh');
    		},
    		error: function(xhr, textStatus, error) {
    			alert("error!: "+textStatus);
    		}
    	});
        return false;
    });
});

$("#contact").on("pageshow",function(e){
	var $button = $(document).find('button');
	$.ajax({
		url: "posts/get-latest-xmessages",
		type: "POST",
		dataType: 'json',
		data: {
			lastMessageId: 1,
			},
		timeout: 10000,  // 単位はミリ秒
		beforeSend: function(xhr, settings) {
			$button.attr('disabled', true);
		},
		complete: function(xhr, textStatus) {
			$button.attr('disabled', false);
		},
		success: function(result, textStatus, xhr) {
			$.each(result, function(i, val) {
				var li = $("<li>"+val.created_at+" "+val.userName+": "+val.message+"</li>");
				li.attr("user-data",JSON.stringify({id: val.id}));
				$('#messages').append(li);
						});
			$(messages).listview('refresh');
		},
		error: function(xhr, textStatus, error) {
			alert("error!: "+textStatus);
		}
	});

	function poll() {
		   setTimeout(function() {
				var postData = {};
				var lastMessageLi = $("#messages li:last");
				if (lastMessageLi.length > 0) {
					postData["lastMessageId"] = JSON.parse(lastMessageLi.attr("user-data")).id ;
				}
		   		$.ajax({
		    		url: "posts/get-latest-messages",
		    		type: "POST",
		    		dataType: 'json',
		    		data: postData,
		    		timeout: 10000,  // 単位はミリ秒
		    		beforeSend: function(xhr, settings) {
		    			$button.attr('disabled', true);
		    		},
		    		complete: function(xhr, textStatus) {
		    			$button.attr('disabled', false);
		    			poll();
		    		},
		    		success: function(result, textStatus, xhr) {
		    			$.each(result, function(i, val) {
		    				var li = $("<li>"+val.created_at+" "+val.userName+": "+val.message+"</li>");
		    				li.attr("user-data",JSON.stringify({id: val.id}));
		    				$('#messages').append(li);
		    			});
		    			$(messages).listview('refresh');
		    		},
		    		error: function(xhr, textStatus, error) {
		    			alert("error!: "+textStatus);
		    		}
		    	});
		   	    }, 10000);
		};
		poll();
});
</script>
<a data-role="button" id="uparrow" style="padding: 0"><img
	src="image/uparrow_midori.png" /></a>
<div data-role="fieldcontain" data-iscroll class="iscroll-wrapper"
	style="width: 100%">
	<div class="iscroll-scroller">
		<!-- If you included a pull-down under the wrapper, it will wind-up here -->
		<div class="iscroll-content">
			<ul id="messages" data-role="listview">
			</ul>
		</div>
	</div>
</div>
<a data-role="button" id="downarrow" style="padding: 0"><img
	src="image/downarrow_midori.png" /></a>
<form action="posts/post-message" method="POST" id="myForm" data-ajax="false">
	<div data-role="fieldcontain" class="ui-hide-label"
		data-type="horizontal" style="">
		<table width="100%">
			<tr>
				<td width="20%"><input type="text" id="userName" name="userName"
					placeholder="名前" data-mini="true" data-inline="true" /></td>
				<td width="60%"><textarea cols="40" rows="1" name="message"
						id="message" placeholder="内容" data-mini="true" data-inline="true"></textarea></td>
				<td width="20%"><a href="#popup" data-rel="popup"
					data-transition="pop" data-role="button" data-icon="plus">画像</a></td>
			</tr>
		</table>
	</div>
	<input type="submit" value="送信" data-inline="true">
</form>
<input type="hidden" name="lastMessageId" id="lastMessageId"/>

@stop
@section('popup')
<div id="popup" data-role="popup" data-dismissible="false"
	style="max-width: 500px;">
	<div data-role="header">
		<h1>画像登録</h1>
	</div>
	<div role="main" class="ui-content">
		<table width="100%">
				<tr>
					<td width="50%"><input type="file" name="file"></td>
					<td width="50%"><a href="#" data-rel="back" data-role="button"
						data-icon="plus">アップロード</a></td>
				</tr>
			</table>
			<a href="#" data-rel="back" data-role="button">閉じる</a>

	</div>
</div>

@stop