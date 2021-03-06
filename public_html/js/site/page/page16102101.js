$(document).on("pagecontainershow.tomomi", pageSetup);

$(document).on("pagecontainerbeforeshow.tomomi", pageDestroy);

$(document).on("popupbeforeposition.tomomi", ".popupImageDiv", function() {
	var maxHeight = $(window).height() - 60 + "px";
	$("img", $(this)).css("max-height", maxHeight);
});

$(document).on("popupbeforeposition.tomomi", ".popupTiraDiv", function() {
	var minWidth = $(window).width() - 100 + "px";
	var maxHeight = $(window).height() - 150 + "px";
	tom.$OC($(this), "messagesDiv").css("max-height", maxHeight).css("min-width", minWidth);
});

$(document).on("popupafteropen.tomomi", ".popupLoginDiv", function() {
	$("input", $(this)).first().focus();
});

$(document).on("click.tomomi", "[name ='pageContentsDiv'] img", pageImageClick);

$(document).on("scroll.tomomi", onPageScroll);

function pageSetup(event, ui) {
	var page = tom.$AP();
	var scope = new Scope();
	tom.$scope(page, scope);

	headerInit(page);

	var messageInputDiv = tom.$OC(page, "messageInputDiv");
	var messagesUl = tom.$OC(page, "messagesUl");
	if (messagesUl.length) {
		messagesUl.listview({
			autodividers : true,
			autodividersSelector : function(li) {
				var out = li.attr("user-data-posted-date");
				return out;
			}
		});

		var url = document.URL;
		var param = {};
		var isAroundMode = false;
		var isPagingMode = false;
		var hitMessageId = null;
		if (document.URL.match(/.+\?around=.+/)) {
			url = document.URL.replace(/^(.+)\?.+$/, "$1") + "/get-messages-around-at";
			var hitMessageId = document.URL.replace(/^.+\?around=(.+)$/, "$1");
			param.around = hitMessageId;
			isAroundMode = true;
			messageInputDiv.empty().append("<b>検索結果表示モードのため投稿はできません。子ページも表示されません。</b><hr>");
		} else if (document.URL.match(/.+\?pagingNo=.+/)) {
			url = document.URL.replace(/^(.+)\?.+$/, "$1") + "/get-messages-paging-at";
			var pagingNo = document.URL.replace(/^.+\?pagingNo=(.+)$/, "$1");
			param.pagingNo = pagingNo;
			isPagingMode = true;
			messageInputDiv.empty().append("<b>ページング表示モードのため投稿はできません。子ページも表示されません。</b><hr>");
		} else {
			url += "/get-latest-xmessages"
		}
		if (!messagesUl.attr("user-data-once-refreshed")) {
			tom.$OC(page, "messageGetOlderA").hide();
			tom.$OC(messagesUl, "messagePollProgressDiv").text("⇔");
			var query = function() {
				scope.simpleAjax(url, param).done(function(result, textStatus, xhr) {
					$("li", messagesUl).remove();
					if (isAroundMode) {
						chatAppendMessagesUl(page, result, "top", true, null, true);
						$hitLi = messagesUl.find("[user-data-posted-id='" + hitMessageId + "']");
						$hitLi.css("border", "2px solid red");
					} else if (isPagingMode) {
						chatAppendMessagesUl(page, result, "top", true, null, true);
					} else {
						chatAppendMessagesUl(page, result, "top", true);
						chatPollMessage(page);
					}
				}).fail(function() {
					var li = $("li", messagesUl);
					li.text(li.text() + ".fail retrying..");
					scope.wait(10000).done(query);
				})
			};
			query();
		} else {
			if (!isAroundMode && !isPagingMode)
				chatPollMessage(page);
		}
	}

	$(":file", page).each(
			function(i, fileInput) {
				fileInput = $(fileInput);
				var masterCtrl = fileInput.prev();
				fileInput.css('opacity', 0).css('position', 'absolute').css("top", 0).css("left", 0).width(masterCtrl.outerWidth()).height(
						masterCtrl.outerHeight());
				fileInput.css("z-index", masterCtrl.css("z-index") ? masterCtrl.css("z-index") : "20");
				fileInput.css("display", "");
			});

	if (messageInputDiv.length && !isAroundMode && !isPagingMode) {

		var messageTxt = tom.$OC(messageInputDiv, "messageTxt");
		var messageSubmitBtn = tom.$OC(messageInputDiv, "messageSubmitBtn");
		var messageSubmitButtonControl = function() {
			if (messageTxt.val().length == 0 || messageSubmitBtn.attr("user-data-submitting")) {
				messageSubmitBtn.button("disable").button("refresh");
			} else {
				messageSubmitBtn.button("enable").button("refresh");
				;
			}
			scope.wait(2000).done(messageSubmitButtonControl);
		};
		messageSubmitButtonControl();
	}

	var lazyImages = $("img.lazy", page).lazyload({
		event : "doLazyLoad"
	});

	tom.$OC(page, "pageEditBodyDiv").wysiwyg();

	var goMessageAnchor = tom.$OC(page, "goMessageAnchor");
	if (goMessageAnchor.length > 0 && messageInputDiv.offset().top > 800) {
		goMessageAnchor.show();
	}

	$(".naviSelected", tom.$AP()).addClass("ui-btn-active");

	var dataVersion = page.attr("data-version");
	if (dataVersion && dataVersion != myJsVersion) {
		pageAlert({
			description : "サイトがバージョン<b>'v." + dataVersion + "'</b>にアップしました。<br><br>お手数ですが<b>ブラウザの再読み込み</b>をお願いします。",
			stack : ""
		});
	}

	var naviSearchDiv = tom.$OC(page, "naviSearchDiv");
	if (naviSearchDiv.length) {
		var siteSearchKeywordTxt = tom.$OC(naviSearchDiv, "siteSearchKeywordTxt");
		var siteSearchBtn = tom.$OC(naviSearchDiv, "siteSearchBtn");
		siteSearchBtn.button();
		var siteSearchBtnControl = function() {
			if (siteSearchKeywordTxt.val().length == 0 || siteSearchBtn.attr("user-data-submitting")) {
				siteSearchBtn.button("disable").button("refresh");
			} else {
				siteSearchBtn.button("enable").button("refresh");
			}
			scope.wait(2000).done(siteSearchBtnControl);
		};
		siteSearchBtnControl();
	}

	var scrollDiv = tom.$APC("scrollDiv");
	if (scrollDiv.length > 0) {
		scrollDiv.show();
		scope.val.scrollDivControlLastScrollTime = new Date();
		scope.val.scrollDivControlLastScrollTop = $(window).scrollTop();
		var pageScrollDivAutoHider = function() {
			if (new Date() - scope.val.scrollDivControlLastScrollTime > 3000) {
				scrollDiv.hide();
			}
			scope.wait(2000).done(pageScrollDivAutoHider);
		}
		scope.wait(2000).done(pageScrollDivAutoHider);
	}

	guidToSSL(page);
}

function pageDestroy(event, ui) {
	var page = ui.prevPage;
	var scope = tom.$scope(page);
	if (scope) {
		scope.destroy();
		tom.$scope(page, null);
	}
}

function headerInit(page, isLoginInit) {
	var menuControlDiv = tom.$OC(page, "menuControl");
	if (menuControlDiv.length > 0) {
		menuControlDiv.removeClass("loged-in not-loged-in query-loged-in");
		menuControlDiv.addClass("query-loged-in");
		logedInUserNameP = tom.$OC(page, "logedInUserNameP");
		logedInUserNameP.hide();
		var imgFace = tom.$OC(page, "imgFace");
		imgFace.removeAttr("src");
		var faceSel = tom.$OC(page, "faceSel");
		faceSel.hide();
		var facesDiv = tom.$OC(page, "faces");
		$("span[data-template!='yes']", facesDiv).remove();
		var messageTxt = tom.$OC(page, "messageTxt");
		messageTxt.attr("placeholder", "内容");

		var scope = tom.$scope(page);
		var queryLogedIn = function() {
			scope.simpleSyncAjax(getSitegUrl() + "/is-user-loged-in", {}).done(function(result, textStatus, xhr) {
				menuControlDiv.removeClass("query-loged-in");
				if (result.userName) {
					menuControlDiv.addClass("loged-in");
					logedInUserNameP.text(result.userName);
					logedInUserNameP.show();
					if (result.faces && result.faces.length > 0) {
						var faces = result.faces;
						imgFace.attr("src", "/" + faces[0]);
						faceSel.show();
						var faceTemplate = $("span[data-template]", facesDiv);
						for (var i = 0; i < faces.length; i++) {
							var newFace = faceTemplate.clone(true);
							var newFaceImg = $("img", newFace);
							newFaceImg.removeAttr("src");
							newFaceImg.attr("data-original", "/" + faces[i]);
							newFace.removeAttr("data-template");
							if (isLoginInit) {
								newFaceImg.lazyload({
									event : "doLazyLoad"
								});
							}
							newFace.show();
							facesDiv.append(newFace);
						}
					}
				} else {
					menuControlDiv.addClass("not-loged-in");
					messageTxt.attr("placeholder", "内容 ※投稿するにはログインが必要です。画面右上からどうぞ");
				}
			}).fail(function(result, textStatus, xhr) {
				scope.wait(5000).done(queryLogedIn);
			});
		};
		queryLogedIn();
	}
}

function chatAppendMessagesUl(page, result, direction, doControlGetOlderA, messagesUl, doControlGetNewerA) {
	if (direction == "bottom")
		result.messages.reverse();
	var authedUserName = result.authedUserName;
	if (authedUserName)
		authedUserName = authedUserName.toLowerCase();
	if (!messagesUl) {
		messagesUl = tom.$OC(page, "messagesUl");
	}
	$
			.each(
					result.messages,
					function(i, val) {
						var lastMessageLi = $("[user-data-posted-id]", messagesUl);
						if (direction == "top" && lastMessageLi.length > 0) {
							if (parseInt(val.id) <= parseInt(lastMessageLi.attr("user-data-posted-id")))
								return;
						}
						var li = $("<li style='margin-bottom:1px'/>")
						var divT = $("<div class='table'/>");
						var divR = $("<div class='row'/>");
						var divC;

						var isOwner = val.userName && (val.userName.toLowerCase() == authedUserName);
						divC = $("<div class='" + (isOwner ? "chatTimeAndNameBoxModOwner" : "") + " chatTimeAndNameBoxBase' style='padding-right:0em;'/>");
						var divCC = $("<div class='table'/>");
						var divCCR = $("<div class='row'/>");
						var divCCCtimeName = $(
								"<div style='padding-right:0em; font-size: 70%; min-width: 5em; max-width: 5em; white-space: normal; word-break: break-all; '/>")
								.append($.format.date(val.updated_at, "HH:mm")).append("<br/>").append(val.userName);
						var faceImg;
						if (val.imgFace)
							faceImg = $("<img style='margin-right:1em; max-height: 3em; max-width: 3em; '/>").attr("src", val.imgFace);
						var emotionImg;
						if (val.imgEmotion) {
							emotionImg = $("<img style='max-height: 2em; max-width: 2em; ' />").attr("src", val.imgEmotion);
							if (faceImg) {
								emotionImg.css({
									position : "absolute",
									bottom : 0,
									right : 0,
								});
							}
						}
						var divCCCfaceEmotionImg = $("<div style='position:relative;padding-right:0em;'/>").append(faceImg).append(emotionImg);
						if (isOwner) {
							divCCR.append(divCCCfaceEmotionImg).append(divCCCtimeName);
						} else {
							divCCR.append(divCCCtimeName).append(divCCCfaceEmotionImg);
						}
						divCC.append(divCCR);
						divC.append(divCC);

						divR.append(divC);
						divC = $("<div style='padding-right:0em;'/>");
						var urlPattern = new RegExp('(https?:\\/\\/' + // protocol
						'((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
						'((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
						'(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
						'(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
						'(\\#[-a-z\\d_]*)?)', 'gi'); // fragment locator
						var messageWithUrl = val.message
								.replace(urlPattern,
										"<a style='text-decoration: underline; background-color: lawngreen; word-break: break-all;' class='' onclick='openAnotherSite(this)'>$1</a>");
						divC.append(messageWithUrl).trigger("create");
						divR.append(divC);

						divT.append(divR);
						li.append(divT);
						if (val.images) {
							divC = $("<div class='shadowImages' />").html(val.images);
							$("img", divC).on("click", chatImageClick);
							li.append(divC);
						}
						if (val.files) {
							divC = $("<div class='attachFiles' />").html(val.files);
							$("a", divC).each(function() {
								var anch = $(this);
								var filename = anch.attr("data-filename");
								var divBox = getAttachFileDivBox();
								divBox.append(filename);
								anch.append(divBox);
								anch.on("click", function() {
									confirmDialog("「" + filename + "」をダウンロードします。<br><br>当サイトではアップロードされたファイルのウィルスチェックは行っていませんのでご留意ください。", function() {
										var downloadForm = $("#downloadForm");
										if (!downloadForm.length) {
											downloadForm = $('<form id="downloadForm" method="post" target="_blank">');
											downloadForm.append('<input type="hidden" name="filename">');
											downloadForm.append('<input type="hidden" name="href">');
											$("body").append(downloadForm);
										}
										tom.$OC(downloadForm, "filename").val(filename);
										tom.$OC(downloadForm, "href").val(anch.attr("data-href"));
										downloadForm.attr("action", document.URL + "/download-attach-file");
										downloadForm.submit();
									});
								});
							});
							li.append(divC);
						}
						li.attr("user-data-posted-id", val.id);
						li.attr("user-data-posted-date", val.updated_at.substr(0, 10));
						if (direction == "bottom") {
							messagesUl.append(li);
						} else {
							messagesUl.prepend(li);
						}
					});
	messagesUl.listview('refresh');
	messagesUl.attr("user-data-once-refreshed", "true");

	tom.$OC(messagesUl, "messagePollProgressDiv").text("at " + $.format.date(new Date(), "HH:mm:ss"));

	if (doControlGetOlderA) {
		var messageGetOlderA = tom.$OC(page, "messageGetOlderA");
		if (result.noMoreOlderMessages) {
			messageGetOlderA.hide();
		} else {
			messageGetOlderA.show();
		}
	}
	if (doControlGetNewerA) {
		var messageGetNewerA = tom.$OC(page, "messageGetNewerA");
		if (result.noMoreNewerMessages) {
			messageGetNewerA.hide();
		} else {
			messageGetNewerA.show();
		}
	}
}

function openAnotherSite(openButton) {
	var url = $(openButton).text();
	confirmDialog("以下の別サイトを開きます。<br>よろしいですか？<br>" + url, function() {
		window.open(url);
	});
}
function getOlderMessage(self) {
	self = $(self);
	var page = tom.$AP();
	var messagesUl = tom.$OC(page, "messagesUl");
	var lastMessageLi = $("li:last", messagesUl);
	if (lastMessageLi.length == 0)
		return;

	var messageGetOlderA = tom.$OC(page, "messageGetOlderA");
	var img = $("img", messageGetOlderA);
	if (!img.attr("data-img-bk")) {
		img.attr("data-img-bk", img.attr("src"));
		img.attr("src", "/image/site/ajax-loader.gif");
	}
	messageGetOlderA.addClass("ui-disabled");

	var postData = {
		olderMessageId : lastMessageLi.attr("user-data-posted-id"),
		boost : self.attr("data-get-boost"),
	};
	var docUrl = document.URL.replace(/^(.+)\?.+$/, "$1");
	var scope = tom.$scope(page);
	var query = function() {
		scope.simpleAjax(docUrl + "/get-older-messages", postData).done(function(result, textStatus, xhr) {
			img.attr("src", img.attr("data-img-bk"));
			img.removeAttr("data-img-bk");
			messageGetOlderA.removeClass("ui-disabled");
			chatAppendMessagesUl(page, result, 'bottom', true);
		}).fail(function() {
			scope.wait(10000).done(query);
		})
	};
	query();
}

function getNewerMessage(self) {
	self = $(self);
	var page = tom.$AP();
	var messagesUl = tom.$OC(page, "messagesUl");
	var lastMessageLi = messagesUl.children(":eq(1)");
	if (lastMessageLi.length == 0)
		return;

	var messageGetNewerA = tom.$OC(page, "messageGetNewerA");
	var img = $("img", messageGetNewerA);
	if (!img.attr("data-img-bk")) {
		img.attr("data-img-bk", img.attr("src"));
		img.attr("src", "/image/site/ajax-loader.gif");
	}
	messageGetNewerA.addClass("ui-disabled");

	var postData = {
		newerMessageId : lastMessageLi.attr("user-data-posted-id"),
		boost : self.attr("data-get-boost"),
	};
	var docUrl = document.URL.replace(/^(.+)\?.+$/, "$1");
	var scope = tom.$scope(page);
	var query = function() {
		scope.simpleAjax(docUrl + "/get-newer-messages", postData).done(function(result, textStatus, xhr) {
			img.attr("src", img.attr("data-img-bk"));
			img.removeAttr("data-img-bk");
			messageGetNewerA.removeClass("ui-disabled");
			var scrollTopBefore = $(window).scrollTop();
			var lastMessageLiTopBefore = lastMessageLi.offset();
			chatAppendMessagesUl(page, result, 'top', false, null, true);
			var lastMessageLiTopAfter = lastMessageLi.offset();
			jQuery.mobile.silentScroll(scrollTopBefore + lastMessageLiTopAfter.top - lastMessageLiTopBefore.top);
		}).fail(function() {
			scope.wait(10000).done(query);
		})
	};
	query();
}

function chatPollMessage(page) {
	var scope = tom.$scope(page);
	if (scope == null)
		return; // page destroyed;
	var messagesUl = tom.$OC(page, "messagesUl");
	var waitTime = 10000;
	// tom.$OC(messagesUl, "messagePollProgressDiv").text("z".repeat(waitTime /
	// 2000));
	scope.wait(waitTime, "chatPollMessage").done(function() {
		var lastMessageLi = messagesUl.children(":eq(1)");
		var postData = {};
		if (lastMessageLi.length > 0) {
			postData["lastMessageId"] = lastMessageLi.attr("user-data-posted-id");
		}
		tom.$OC(messagesUl, "messagePollProgressDiv").text("⇔");
		scope.simpleAjax(document.URL.replace(/#.*$/, '') + "/get-latest-messages", postData).done(function(result, textStatus, xhr) {
			chatAppendMessagesUl(page, result, "top", false)
		}).fail(function() {
			tom.$OC(messagesUl, "messagePollProgressDiv").text("fail.retrying...");
		}).always(function() {
			chatPollMessage(page);
		})
	}).progress(function(remainTime) {
		// tom.$OC(messagesUl, "messagePollProgressDiv").text("z".repeat(remainTime
		// / 2000));
	});
}

function postMessage(self) {
	var messageSubmitBtn = $(self);
	var page = tom.$AP();
	var messageInputDiv = tom.$OC(page, "messageInputDiv");
	var messageTxt = tom.$OC(messageInputDiv, "messageTxt");
	if (messageTxt.val().length == 0 || messageSubmitBtn.attr("user-data-submitting")) {
		messageSubmitBtn.button("disable").button("refresh");
		return false;
	}
	messageSubmitBtn.attr("user-data-submitting", true);
	var chatAttachImagesDiv = tom.$OC(messageInputDiv, "chatAttachImagesDiv");
	var chatAttachImagesStr = "";
	$("img", chatAttachImagesDiv).each(function() {
		if (chatAttachImagesStr)
			chatAttachImagesStr += " ";
		chatAttachImagesStr += this.outerHTML;
	});
	var chatAttachFilesDiv = tom.$OC(messageInputDiv, "chatAttachFilesDiv");
	var chatAttachFileNames = [];
	var chatAttachFileContents = [];
	$.each(tom.$OC(chatAttachFilesDiv, "fileDiv"), function(i, val) {
		var fileDiv = $(this);
		chatAttachFileNames.push(fileDiv.attr("data-filename"));
		chatAttachFileContents.push(fileDiv.attr("data-contents"));
	});
	var lastMessageLi = tom.$OC(page, "messagesUl").children(":eq(1)");
	var imgFace = tom.$OC(messageInputDiv, "imgFace");
	if (!imgFace.attr("data-no-settings")) {
		var imgFaceVal = imgFace.attr("src");
	}
	var imgEmotion = tom.$OC(messageInputDiv, "imgEmotion");
	if (!imgEmotion.attr("data-no-settings")) {
		var imgEmotionVal = imgEmotion.attr("src");
	}
	var postData = {
		message : messageTxt.val(),
		imgFace : imgFaceVal,
		imgEmotion : imgEmotionVal,
		attachImages : chatAttachImagesStr,
		attachFilesName : chatAttachFileNames,
		attachFilesContents : chatAttachFileContents,
		lastMessageId : lastMessageLi.attr("user-data-posted-id"),
	}
	messageSubmitBtn.val("送中..").button("refresh");
	var scope = tom.$scope(page);
	scope.removeWaitByKey("chatPollMessage");
	tom.$OC(page, "messagePollProgressDiv").text("⇒");
	scope.simpleAjax(document.URL + "/add-message", postData).done(function(result, textStatus, xhr) {
		if (xhr.status == "202" && result.r == "are you human?") {
			messageSubmitBtn.val("送").button("refresh");
			confirmDialog("投稿するにはログインが必要です。<br>今すぐログイン(新規登録もこちら)しますか？", function() {
				loginPopup(messageSubmitBtn[0]);
			});
		} else {
			messageTxt.val("");
			messageTxt.textinput("refresh");
			chatAttachImagesDiv.html("");
			chatAttachFilesDiv.html("");
			messageSubmitBtn.val("送").button("refresh");
			chatAppendMessagesUl(page, result, "top", false)
		}
	}).fail(function(result, textStatus, xhr) {
		messageSubmitBtn.val("err:" + textStatus).button("refresh");
	}).always(function() {
		messageSubmitBtn.removeAttr("user-data-submitting");
		chatPollMessage(page);
	});
}

function openEmotions(event) {
	var page = tom.$AP();
	var popupDiv = tom.$OC(page, "emotionsPopupDiv");
	popupDiv.popup("open", {
		x : event.pageX,
		y : event.pageY,
	})
	$("img.lazy", popupDiv).trigger("doLazyLoad")
}

function setEmotion(event) {
	var page = tom.$AP();
	var imgTarget = tom.$OC(page, "imgEmotion");
	var imgSelected = $("img", event.currentTarget);
	imgTarget.attr({
		src : imgSelected.attr("src"),
		'data-no-settings' : imgSelected.attr("data-no-settings") ? "Y" : "",
	});
	var popupDiv = tom.$OC(page, "emotionsPopupDiv");
	popupDiv.popup("close");
}

function openFaces(event) {
	var page = tom.$AP();
	var popupDiv = tom.$OC(page, "facesPopupDiv");
	popupDiv.popup("open", {
		x : event.pageX,
		y : event.pageY,
	})
	$("img.lazy", popupDiv).trigger("doLazyLoad")
}

function setFace(event) {
	var page = tom.$AP();
	var imgTarget = tom.$OC(page, "imgFace");
	var imgSelected = $("img", event.currentTarget);
	imgTarget.attr({
		src : imgSelected.attr("src"),
		'data-no-settings' : imgSelected.attr("data-no-settings") ? "Y" : "",
	});
	var popupDiv = tom.$OC(page, "facesPopupDiv");
	popupDiv.popup("close");
}

function savePageEdit(self) {
	var pageEditSaveBtn = $(self);
	try {
		if (pageEditSaveBtn.attr("user-data-submitting")) {
			return false;
		}
		var page = tom.$AP();
		var scope = tom.$scope(page);
		var titleInp = tom.$OC(page, "titleInp");
		var titleTxt = titleInp.val();
		var bodyDiv = tom.$OC(page, "pageEditBodyDiv");
		var bodyHtml = bodyDiv.html();
		var thumbnailImg = tom.$OC(page, "thumbnailImg");
		var thumbnailSrc = thumbnailImg.attr("src");
		var hasChatSelect = tom.$OC(page, "hasChatSelect");
		var isPublicSelect = tom.$OC(page, "isPublicSelect");
		var pageEditDiv = tom.$OC(page, "pageEdit");
		var isNewPage = pageEditDiv.attr("data-isNewPage");
		var parentPageBtn = tom.$OC(page, "parentPageBtn");
		var parentId = parentPageBtn.attr("data-parentId");

		pageEditSaveBtn.attr("user-data-submitting", true);
		pageEditSaveBtn.val("更新中...");
		pageEditSaveBtn.button("disable").button("refresh");
		var postData = {
			title : titleTxt,
			thumbnail : thumbnailSrc,
			body : bodyHtml,
			hasChat : hasChatSelect.val(),
			isPublic : isPublicSelect.val(),
			isNewPage : isNewPage,
			parent : parentId,
		};
		scope.simpleAjax("save-page-edit", postData).done(function(result, textStatus, xhr) {
			if (xhr.status == "202" && result.r == "are you human?") {
				loginPopup(pageEditSaveBtn, function() {
					savePageEdit(self);
				});
			} else {
				if (isNewPage) {
					pageEditSaveBtn.val("以降の修正は、一度戻ってﾍﾟｰｼﾞ編集ﾒﾆｭｰ!");
				} else {
					pageEditSaveBtn.val("更新しました");
					pageEditSaveBtn.button("enable");
				}
				pageEditSaveBtn.button("refresh");
				thumbnailImg.attr("src", result.thumbnail);
				bodyDiv.html(result.body);
				pageEditDiv.attr("data-isNewPage", "");
			}
		}).fail(function(result, textStatus, xhr) {
			if (result.responseJSON && result.responseJSON.error.message) {
				pageEditSaveBtn.val("更新失敗(;; " + result.responseJSON.error.message);
			} else {
				pageEditSaveBtn.val("更新失敗(;; " + textStatus + ":" + result.statusText);
			}
			pageEditSaveBtn.button("enable").button("refresh");
		}).always(function() {
			pageEditSaveBtn.removeAttr("user-data-submitting");
		});
	} catch (e) {
		pageAlert(e);
	}
}

function saveProfileEdit(self) {
	var editProfileSaveBtn = $(self);
	try {
		if (editProfileSaveBtn.attr("user-data-submitting")) {
			return false;
		}
		var page = tom.$AP();
		var scope = tom.$scope(page);
		var profileUserNameInp = tom.$OC(page, "profileUserNameInp");
		var profileUserNameStr = profileUserNameInp.val().trim();
		if (!profileUserNameStr) {
			var popupDiv = tom.$OC(page, "userNameRequirePopup");
			popupDiv.popup("open", {
				positionTo : profileUserNameInp,
			});
			return;
		}
		var profileAttachImagesDiv = tom.$OC(page, "profileAttachImagesDiv");
		var profileAttachImagesStr = "";
		$("img", profileAttachImagesDiv).each(function() {
			if (profileAttachImagesStr)
				profileAttachImagesStr += " ";
			profileAttachImagesStr += this.outerHTML;
		});

		editProfileSaveBtn.attr("user-data-submitting", true);
		editProfileSaveBtn.val("更新中...");
		editProfileSaveBtn.button("disable").button("refresh");
		var postData = {
			userName : profileUserNameStr,
			attachImagesHtml : profileAttachImagesStr,
		};
		scope
				.simpleAjax("save-profile-edit", postData)
				.done(
						function(result, textStatus, xhr) {
							if (xhr.status == "202") {
								if (result.r == "are you human?") {
									loginPopup(editProfileSaveBtn[0], function() {
										saveProfileEdit(editProfileSaveBtn, scope);
									});
								} else {
									editProfileSaveBtn.val("更新失敗: " + result.errorMsg);
									editProfileSaveBtn.button("enable").button("refresh");
								}
							} else {
								editProfileSaveBtn.val("更新しました");
								editProfileSaveBtn.button("enable").button("refresh");
								profileAttachImagesDiv.empty();
								$
										.each(
												result.imageSrcs,
												function(i, val) {
													var image = $("<img>").attr("src", val);
													var table = $(' <table border="0" style="display:inline-block"><tr><td><a name="moveImageBtn" data-role="button" data-icon="arrow-left" onclick="moveImageInDiv(event)">&nbsp;</a></td></tr><tr><td name="imageTd"></td></tr><tr><td><a name="delImageBtn" data-role="button" onclick="deleteImageFromDiv(event)">削除</a></td></tr></table> ');
													tom.$OC(table, "imageTd").append(image);
													profileAttachImagesDiv.append(table);
													table.trigger("create");
												});
								hideShowMoveImageBtn(profileAttachImagesDiv);
							}
						}).fail(function(result, textStatus, xhr) {
					editProfileSaveBtn.val("更新失敗(;; " + textStatus + ":" + result.statusText);
					editProfileSaveBtn.button("enable").button("refresh");
				}).always(function() {
					editProfileSaveBtn.removeAttr("user-data-submitting");
				});
	} catch (e) {
		pageAlert(e);
	}
}

function openHeaderMenu(event) {
	event.preventDefault();
	var page = tom.$AP();
	var popupDiv = tom.$OC(page, "popupMenu");
	popupDiv.popup("open", {
		positionTo : $(event.target)
	})
}

function chatAppendImage(event, self) {
	var page = tom.$AP();
	var messageInputDiv = tom.$OC(page, "messageInputDiv");
	var chatAttachImagesDiv = tom.$OC(messageInputDiv, "chatAttachImagesDiv");
	var chatAttachFilesDiv = tom.$OC(messageInputDiv, "chatAttachFilesDiv");
	appendImageToDiv(event, 200, null, chatAttachImagesDiv, chatAttachFilesDiv, 10);
}

function chatImageClick(event) {
	var img = $(this);
	popupImageWindow(img, img.attr("data-base-image"), event);
}

function pageImageClick(event) {
	var img = $(this);
	popupImageWindow(img, img.attr("src"), event);
}

function popupImageWindow(img, src, event) {
	var popupImageDiv = img.next();
	if (popupImageDiv.length == 0 || !popupImageDiv.attr("data-popupImgDiv")) {
		var page = tom.$AP();
		var popupImageDiv = tom.$OC(page, "popupImageDiv");
		popupImageDiv = popupImageDiv.clone(true);
		var popupImg = $("img", popupImageDiv);
		img.after(popupImageDiv);
		popupImg.attr("src", src);
		popupImageDiv.popup();
		popupImageDiv.show();
		popupImg.on("load", function() {
			popupImageDiv.popup("reposition", {
				positionTo : "window"
			});
		})
		popupImageDiv.attr("data-popupImgDiv", "Y");
		popupImageDiv.removeAttr("name");
	}
	var scope = tom.$scope(page);
	popupImageDiv.popup("open");
}

function profileAppendImage(event, self) {
	appendImageToDiv(event, 48);
}

function appendImageToDiv(event, prefferedSize, callback, imagesDiv, filesDiv, maxCount) {
	var maxSize = 10 * 1024 * 1024;
	var fileInp = event.target;
	var imagesDiv = imagesDiv ? imagesDiv : $(fileInp).next(":first");
	var sizeOverFiles = [];
	var sizeZeroFiles = [];
	var invalidExtFiles = [];
	var countOverFiles = [];
	var startCount = tom.$OC(imagesDiv, "imageTd").length + tom.$OC(filesDiv, "fileTd").length;
	$
			.each(
					fileInp.files,
					function(idx, fileInfo) {
						if (fileInfo.size == 0) {
							sizeZeroFiles.push(fileInfo.name);
							return;
						}
						if (fileInfo.size > maxSize) {
							sizeOverFiles.push(fileInfo.name);
							return;
						}
						if (maxCount && startCount + idx + 1 > maxCount) {
							countOverFiles.push(fileInfo.name);
							return;
						}
						var isImageFile = false;
						var fileext = fileInfo.name.replace(/^.+\.(.+)$/, "$1");
						var fileextLower = fileext.toLowerCase();
						switch (fileextLower) {
						case "jpg":
						case "jpeg":
						case "png":
						case "gif":
							isImageFile = true;
							break;
						case "psd":
						case "ai":
						case "pdf":
						case "txt":
							if (filesDiv && filesDiv.length > 0) {
								break;
							}
						default:
							invalidExtFiles.push(fileInfo.name + "(拡張子=" + fileext + ")");
							return;
						}
						$
								.when(readFileIntoDataUrl(fileInfo))
								.done(
										function(dataUrl) {
											if (!/^data:.*?;[Bb][Aa][Ss][Ee]64,.*$/.test(dataUrl)) {
												pageAlert({
													description : "取り扱えないファイルエンコーディング'" + dataUrl.replace(/^(data:.*?;[Bb][Aa][Ss][Ee]64),.*$/, "$1") + "'のため添付できません",
													stack : fileInfo.name
												});
											}
											if (isImageFile) {
												var image = $("<img>").attr("src", dataUrl);
												image.hide();
												var table = $('<table border="0" style="display:inline-block"><tr><td><a name="moveImageBtn" data-role="button" data-icon="arrow-left" onclick="moveImageInDiv(event)">&nbsp;</a></td></tr><tr><td name="imageTd"></td></tr><tr><td><a name="delImageBtn" data-role="button" onclick="deleteImageFromDiv(event)" style="margin-top:0;">削除</a></td></tr></table>');
												tom.$OC(table, "imageTd").append(image);
												imagesDiv.append(table);
												table.trigger("create");
												hideShowMoveImageBtn(imagesDiv);
												var page = tom.$AP();
												tom.$scope(page).wait(0).done(function() {
													autoResize(image, prefferedSize, prefferedSize);
													image.show();
													if (callback)
														callback();
												});
											} else {
												var table = $('<table border="0" style="display:inline-block"><tr><td name="fileTd" style="vertical-align:bottom;"></td></tr><tr><td><a name="delImageBtn" data-role="button" onclick="deleteImageFromDiv(event)" style="margin-top:0;">削除</a></td></tr></table>');
												var fileDiv = getAttachFileDivBox();
												fileDiv.append(fileInfo.name);
												fileDiv.attr("data-contents", dataUrl).attr("data-filename", fileInfo.name);
												tom.$OC(table, "fileTd").append(fileDiv);
												filesDiv.append(table)
												table.trigger("create");
											}
										}).fail(function(e) {
									pageAlert(e);
								});
					});
	if (countOverFiles.length > 0) {
		pageAlert({
			description : "添付個数が" + maxCount + "を超えたため添付できません",
			stack : countOverFiles.join(", <br>")
		});
	}
	if (sizeZeroFiles.length > 0) {
		pageAlert({
			description : "ファイルサイズが０のファイルは添付できません",
			stack : sizeZeroFiles.join(", <br>")
		});
	}
	if (sizeOverFiles.length > 0) {
		pageAlert({
			description : "ファイルサイズの上限を超えたファイルは添付できません",
			stack : sizeOverFiles.join(", <br>")
		});
	}
	if (invalidExtFiles.length > 0) {
		pageAlert({
			description : "取り扱えないファイル拡張子のため添付できません",
			stack : invalidExtFiles.join(", <br>")
		});
	}
	$(fileInp).replaceWith($(fileInp).clone(true));// reset file input
}
function getAttachFileDivBox() {
	return $("<div name='fileDiv' style='background-image: url(\"/image/site/attachFile.png\"); background-repeat: no-repeat; background-position: 1em 0; display:inline-block; width: 8em; min-height: 4em; font-size: 80%; word-wrap: break-word; padding-top:1em; vertical-align:bottom;'>");
}
function hideShowMoveImageBtn(imageDiv) {
	var moveBtns = $("[name='moveImageBtn']", imageDiv);
	$.each(moveBtns, function(i, val) {
		if (i == 0)
			$(val).hide();
		else
			$(val).show();
	});
}
function deleteImageFromDiv(event) {
	var delBtn = $(event.target);
	var table = delBtn.closest("table");
	var imageDiv = table.parent();
	table.remove();
	hideShowMoveImageBtn(imageDiv);
}
function moveImageInDiv(event) {
	var moveBtn = $(event.target);
	var table = moveBtn.closest("table");
	var prevTable = table.prev();
	var parentDiv = table.parent();
	table.remove();
	prevTable.before(table);
	hideShowMoveImageBtn();
}

function refreshLatestPagesAndMessages(topN) {
	var page = tom.$AP();
	var scope = tom.$scope(page);
	var ulLatestPagesAndMessages = tom.$OC(page, "ulLatestPagesAndMessages");
	var ckbLatestPagesAndMessagesIncludePage = tom.$OC(page, "ckbLatestPagesAndMessagesIncludePage");
	var lis = $("li", ulLatestPagesAndMessages);
	$("img", lis).attr("src", "/image/site/ajax-loader.gif");
	$("a", lis).removeAttr("href").css("color", "lightgrey");
	tom.$OC(page, "refreshLatestPagesAndMessagesDiv").find("a").each(function() {
		var a = $(this);
		a.attr("data-textbk", a.text()).text("問合せ中.").addClass("ui-disabled");
	});
	var query = function(isRetry) {
		if (isRetry) {
			tom.$OC(page, "refreshLatestPagesAndMessagesDiv").find("a").each(function() {
				var a = $(this);
				a.text(a.text() + ".");
			});
		}
		scope.simpleAjax(document.URL + "/get-latest-pages-and-messages", {
			topN : topN,
			includePage : ckbLatestPagesAndMessagesIncludePage.prop('checked'),
		}).done(function(result, textStatus, xhr) {
			tom.$OC(page, "refreshLatestPagesAndMessagesDiv").find("a").each(function() {
				var a = $(this);
				a.text(a.attr("data-textbk")).removeAttr("data-textbk").removeClass("ui-disabled");
			});
			ulLatestPagesAndMessages.empty();
			$.each(result.latestUpdated, function(i, val) {
				var li = $("<li>");
				var a = $("<a/>").attr("href", val.id);
				var imgThumbnail = $("<img>").attr("src", val.thumbnail);
				a.append(imgThumbnail);
				a.append(val.title);
				a.append("<br/>");
				var divDesc = $("<div>");
				divDesc.attr("style", "margin-left:1em; font-weight:normal; font-size: small; overflow:hidden; text-overflow:ellipsis;");
				divDesc.append(val.updated_at + " " + val.updated_by + "<br/>" + val.message);
				a.append(divDesc);
				var a2;
				if (val.type == 1) {
					a2 = $("<a href='#'/>").on('click', function() {
						tiraMessage(this);
					});
				}
				li.append(a);
				if (a2)
					li.append(a2);
				li.attr("data-pageId", val.id);
				li.attr("data-pageThumbnail", val.thumbnail);
				li.attr("data-pageTitle", val.title);
				ulLatestPagesAndMessages.append(li);
			});
			ulLatestPagesAndMessages.listview('refresh');
		}).fail(function(result, textStatus, xhr) {
			scope.wait(5000).done(function() {
				query(true);
			});
		});
	};
	query();
}

function logout(self, event) {
	var page = tom.$AP();
	var scope = tom.$scope(page);
	self = $(self);
	var popupDiv = tom.$OC(page, "popupLogoutDiv");
	scope.simpleAjax(getSitegUrl() + "/logout", {}).always(function(result, textStatus, xhr) {
		if (xhr.status == "200") {
			popupDiv.popup("close");
			headerInit(page);
		} else {
			self.text("error:" + textStatus);
		}
	});
}

function loginPopup(self, loginCallback) {
	var page = tom.$AP();
	var scope = tom.$scope(page);
	var popupDiv = tom.$OC(page, "popupLoginDiv");
	var captchaImgDiv = tom.$OC(popupDiv, "captchaImgDiv");
	captchaImgDiv.empty();
	captchaImgDiv.append($("<img style='padding:0; margin:0;' width='220' height='50'>").attr("src", "/captcha?" + myuuid()));
	var inpCaptcha = $("input", popupDiv);
	inpCaptcha.val("");
	var btnCaptcha = $("button", popupDiv);
	btnCaptcha.text("認証");
	popupDiv.popup("open");
	scope.vals.loginCallback = loginCallback;
}

function login(self, event) {
	var page = tom.$AP();
	var scope = tom.$scope(page);
	self = $(self);
	var popupDiv = tom.$OC(page, "popupLoginDiv");
	var captchaInp = tom.$OC(popupDiv, "captchaInp");
	var userNameInp = tom.$OC(popupDiv, "userNameInp");
	var captchaImgDiv = $("[name='captchaImgDiv']", popupDiv);
	// .replace(/#.*$/, "").replace(/^(.*\/site\/.*?\/.*?)\/.*/, "$1")
	scope.simpleAjax(getSitegUrl() + "/login", {
		userName : userNameInp.val(),
		captcha : captchaInp.val()
	}).always(function(result, textStatus, xhr) {
		if (xhr.status == "200") {
			popupDiv.popup("close");
			if (scope.vals.loginCallback) {
				scope.wait(0).done(scope.vals.loginCallback);
			}
			delete scope.vals.loginCallback;
			headerInit(page, true);
			if (!result.isExistUser) {
				var welcomeDiv = tom.$OPC(page, "pageDiv", "popupWelcomeDiv")
				tom.$OC(welcomeDiv, "siteNameSpn").text(result.siteName);
				tom.$OC(welcomeDiv, "userNameSpn").text(result.userName);
				welcomeDiv.popup("open");
			}
		} else if (xhr.status == "202" && result.r == "are you human?") {
			self.text("エラー:再入力");
			captchaImgDiv.empty();
			captchaImgDiv.append($("<img width='220' height='50'>").attr("src", "/captcha?" + myuuid()));
			captchaInp.val("");
			captchaInp.focus();
		} else {
			self.text("error:" + textStatus);
		}
	});
}

function getSitegUrl() {
	return document.URL.replace(/(.*)\/site\/(.*?)\/.*/, "$1/siteg/$2");
}

function pageAlert(e) {
	var page = tom.$AP();
	var popupAlertDiv = tom.$OC(page, "popupAlertDiv");
	popupAlertDiv.html(e.description + "<br><pre>" + e.stack + "</pre>");
	popupAlertDiv.popup("open");
}

function popupPageEditSelectParent(event) {
	var page = tom.$AP();
	var popup = tom.$OC(page, "popupParentPageSelectDiv");
	popup.popup("open", {
		x : event.pageX,
		y : event.pageY,
	})
}
function pageEditSelectParent(self) {
	self = $(self);
	var page = tom.$AP();
	var popup = tom.$OC(page, "popupParentPageSelectDiv");
	popup.popup("close");

	var li = self.closest("li");
	var parentId = li.attr("data-parentId");
	var imgSrc = $("img", li).attr("src");

	var parentPageBtn = tom.$OC(page, "parentPageBtn");
	parentPageBtn.html(self.html());
	parentPageBtn.attr("data-parentId", li.attr("data-pageId"));
}

function popupMovieWindow(src) {
	var page = tom.$AP();
	var popupMovieDiv = tom.$APC("popupMovieDiv");
	var movieDiv = tom.$OC(popupMovieDiv, "movieDiv");
	movieDiv.empty();
	CreateSwfPlayer("movie", src, movieDiv[0]);
	popupMovieDiv.popup();
	popupMovieDiv.show();
	popupMovieDiv.popup("open");
}

function showSiteMap(showDivName, naviThis) {
	topNaviShow(showDivName, naviThis);
	var siteMapDiv = tom.$APC("naviSiteMpDiv");
	if (siteMapDiv.hasClass("siteMapInited"))
		return;
	$(naviThis).addClass("ui-disabled");

	var scope = tom.$scope(tom.$AP());
	var query = function() {
		scope
				.simpleAjax(document.URL + "/get-all-pages")
				.done(
						function(result, textStatus, xhr) {
							var allPagesDef = result.allPages;
							var rootPage;
							var pageMap = {};
							for (var i = 0; i < allPagesDef.length; i++) {
								var curPage = allPagesDef[i];
								pageMap[curPage.id] = curPage;
								if (curPage.parent == 0) {
									rootPage = curPage;
								}
							}
							for (var i = 0; i < allPagesDef.length; i++) {
								var curPage = allPagesDef[i];
								var parent = pageMap[curPage.parent];
								if (parent) {
									if (!parent.childs)
										parent.childs = [];
									parent.childs.push(curPage);
								}
							}
							allPagesDef = null;

							var printPage = function(page) {
								var ret;
								if (page.isDefault) {
									ret = $("<div>").append("サイトマップ");
									ret.append($("<button data-mini='true' data-inline='true' data-icon='plus'>").append("全展開").on("click", function() {
										var childUl = $("ul", $("ul", siteMapDiv));
										childUl.show();
										var allExpandBtn = $(".expandBtn", siteMapDiv);
										allExpandBtn.removeClass("ui-icon-plus");
										allExpandBtn.addClass("ui-icon-minus");
									}));
									ret.append($("<button data-mini='true' data-inline='true' data-icon='minus'>").append("全省略").on("click", function() {
										var childUl = $("ul", $("ul", siteMapDiv));
										childUl.hide();
										var allExpandBtn = $(".expandBtn", siteMapDiv);
										allExpandBtn.removeClass("ui-icon-minus");
										allExpandBtn.addClass("ui-icon-plus");
									}));
								} else {
									var img = $("<img>").attr("src", page.thumb).attr("align", "left");
									var anc = $("<a data-role='button' data-icon='carat-r' data-iconpos='right'>").attr("href", page.id);
									anc.append(img).append(page.title).append("<br/>").append(
											$("<span>").css("font-weight", "normal").append(
													"最終更新:" + page.updatedAt + " by " + page.updatedBy + "<br/> 最終メッセージ:" + page.lastMessageAt));
									var expandBottn = "";
									if (page.childs) {
										expandBottn = $("<button class='expandBtn' onclick='expandSiteMap(this)' style='position:absolute; top:0.3em; left:-2.3em; width:2.3em; height:3em; padding:0; margin:0;' data-icon='plus'>");
									}
									ret = $("<li style='position:relative;'>").append(expandBottn).append(anc);
								}
								if (page.childs) {
									$.each(page.childs, function(i, page) {
										var childUl = $("<ul class='ulLatestPagesAndMessages'>");
										childUl.append(printPage(page));
										ret.append(childUl);
									});
								}
								return ret;
							}
							siteMapDiv.empty();
							siteMapDiv.append(printPage(rootPage));
							siteMapDiv.enhanceWithin();
							siteMapDiv.addClass("siteMapInited");
							$(naviThis).removeClass("ui-disabled");
							$("ul", $("ul", siteMapDiv)).hide();
						}).fail(function() {
					siteMapDiv.children().append("fail retrying..");
					scope.wait(10000).done(query);
				})
	};
	query();
}

function topNaviShow(showDivName, naviThis) {
	$(".topNavi", tom.$AP).hide();
	tom.$APC(showDivName).show();

	$("a", tom.$APC("topNavi")).removeClass("naviSelected");
	$(naviThis).addClass("naviSelected");
}

function expandSiteMap(btnThis) {
	var btn = $(btnThis);
	var childUl = btn.parent().children("ul");
	if (btn.hasClass("ui-icon-plus")) {
		childUl.show();
		btn.removeClass("ui-icon-plus");
		btn.addClass("ui-icon-minus");
	} else {
		childUl.hide();
		btn.removeClass("ui-icon-minus");
		btn.addClass("ui-icon-plus");
	}
}

function tiraMessage(aThis) {
	var li = $(aThis).parent();
	var pageId = li.attr("data-pageId");
	var pageThumbnail = li.attr("data-pageThumbnail");
	var pageTitle = li.attr("data-pageTitle");
	var popupTiraDiv = tom.$APC("popupTiraDiv");
	var tiraMessagesUl = tom.$OC(popupTiraDiv, "tiraMessagesUl");
	var tileTitlebar = tom.$OC(popupTiraDiv, "titlebar");
	tileTitlebar.empty();
	tileTitlebar.append('<img style="width:3em" src="' + pageThumbnail + '" align="left"/>').append(pageTitle);
	$("li", tiraMessagesUl).remove();
	tiraMessagesUl.append("<li>loading...</li>");
	popupTiraDiv.popup();
	popupTiraDiv.show();
	popupTiraDiv.popup("open");

	if (!tiraMessagesUl.attr("data-listview-inited")) {
		tiraMessagesUl.listview({
			autodividers : true,
			autodividersSelector : function(li) {
				var out = li.attr("user-data-posted-date");
				return out;
			}
		});
		tiraMessagesUl.attr("data-listview-inited", "true");
	}

	tom.$OC(tiraMessagesUl, "messagePollProgressDiv").text("⇔");
	var page = tom.$AP();
	var scope = tom.$scope(page);
	var query = function() {
		scope.simpleAjax(document.URL.replace(/^(.*\/).*$/, "$1") + pageId + "/get-latest-xmessages", {}).done(function(result, textStatus, xhr) {
			$("li", tiraMessagesUl).remove();
			chatAppendMessagesUl(page, result, "top", false, tiraMessagesUl);
			popupTiraDiv.popup("reposition", {
				positionTo : "window"
			});
			popupTiraDiv.popup("reposition", {
				positionTo : "window"
			});
		}).fail(function() {
			var li = $("li", tiraMessagesUl);
			li.text(li.text() + ".fail retrying..");
			scope.wait(10000).done(query);
		})
	};
	query();
}

function confirmDialog(message, callback) {
	var confirmDiv = tom.$APC('popupConfirmDiv');
	tom.$OC(confirmDiv, "okButton").off("click").on("click", function() {
		confirmDiv.popup('close');
		callback();
	});
	tom.$OC(confirmDiv, "message").empty().append(message);
	confirmDiv.popup();
	confirmDiv.popup('open');
}

function siteSearch(siteSearchBtn) {
	var page = tom.$AP();
	var scope = tom.$scope(page);
	siteSearchBtn = $(siteSearchBtn);
	var siteSearchKeywordTxt = tom.$OC(page, "siteSearchKeywordTxt");
	var searchKeyword = siteSearchKeywordTxt.val().replace(/^[\s　]+|[\s　]+$/g, "");
	siteSearchKeywordTxt.val(searchKeyword);

	if (searchKeyword.length == 0) {
		pageAlert({
			description : "空白文字だけの検索条件は指定できません",
			stack : ""
		});
		return false;
	}

	if (siteSearchBtn.attr("user-data-submitting")) {
		siteSearchBtn.button("disable").button("refresh");
		return false;
	}
	siteSearchBtn.attr("user-data-submitting", true);

	var postData = {
		searchKeyword : searchKeyword,
	}
	siteSearchBtn.val("検中..").button("refresh");
	var searchResultDiv = tom.$OC(page, "searchResultDiv");
	searchResultDiv.empty().append($('<image src="/image/site/ajax-loader.gif">'));

	scope.simpleAjax(getSitegUrl() + "/site-search", postData).done(function(result, textStatus, xhr) {
		siteSearchBtn.val("検索").button("refresh");
		searchResultDiv.empty();
		if (result.searchResults.length == 0) {
			searchResultDiv.append("一致する情報は見つかりませんでした");
			return;
		}
		var keywords = result.keywords;
		var hitPageInfo = {};
		$.each(result.hitPageInfo, function(i, val) {
			hitPageInfo[val.id] = val;
		});

		$.each(result.searchResults, function(i, val) {
			var divHit = $('<div class="searchResult">');
			var hitPage = hitPageInfo[val.pageId];
			var anch = $('<a>').attr("href", hitPage.id + ((val.type == 1) ? "" : "?around=" + val.messageId));
			var pageTitle = hitPage.title;
			if (val.type == 1) {
				pageTitle = highlightSearchKeyword(pageTitle, keywords);
			}
			var title = $('<div class="searchResultTitle">').append("[" + (i + 1) + "] ").append(pageTitle);
			anch.append(title);
			var subTitle = $('<div class="searchResultSubtitle">').append(val.updated_at + " " + val.userName);
			anch.append(subTitle);
			divHit.append(anch);
			var message = val.message;
			if (!message)
				message = "";
			var foundPos = Number.MAX_VALUE;
			$.each(keywords, function(i, keyword) {
				foundPos = Math.min(foundPos, message.indexOf(keyword));
			});
			if (foundPos == Number.MAX_VALUE) {
				foundPos = 0;
			}
			var MESSAGE_DISP_MAX_LEN = 100;
			var cutFrom = Math.max(0, foundPos - MESSAGE_DISP_MAX_LEN / 2);
			message = message.substring(cutFrom);
			if (message.length > MESSAGE_DISP_MAX_LEN) {
				message = message.substr(0, MESSAGE_DISP_MAX_LEN) + "......";
			}
			if (cutFrom > 0) {
				message = "......" + message;
			}
			message = highlightSearchKeyword(message, keywords);
			var hitTable = $('<table>');
			var hitTr = $('<tr>');
			var hitTd1 = $('<td>');
			switch (val.type) {
			case "1":
				hitTd1.append("ページ<br>");
				hitTd1.append($('<img style="max-height: 4em; max-width: 4em; ">').attr('src', hitPageInfo[val.pageId].thumb));
				break;
			case "2":
				hitTd1.append("ﾒｯｾｰｼﾞ<br>");
				var faceImg = null;
				if (val.imgFace)
					faceImg = $("<img style='margin-right:1em; max-height: 3em; max-width: 3em; '/>").attr("src", val.imgFace);
				var emotionImg = null;
				if (val.imgEmotion) {
					emotionImg = $("<img style='max-height: 2em; max-width: 2em; ' />").attr("src", val.imgEmotion);
					if (faceImg) {
						emotionImg.css({
							position : "absolute",
							bottom : 0,
							right : 0,
						});
					}
				}
				var divFace = $("<div style='min-width: 4em; position:relative;padding-right:0em;'/>").append(faceImg).append(emotionImg);
				hitTd1.append(divFace);
				break;
			}
			hitTr.append(hitTd1);
			var hitTd2 = $('<td>');
			hitTd2.append(message);
			hitTr.append(hitTd2);
			hitTable.append(hitTr);
			var hitContents = $('<div class="searchResultContents">').append(hitTable);
			divHit.append(hitContents);
			searchResultDiv.append(divHit);
		});
	}).fail(function(result, textStatus, xhr) {
		siteSearchBtn.val("err:" + textStatus).button("refresh");
		searchResultDiv.empty();
	}).always(function() {
		siteSearchBtn.removeAttr("user-data-submitting");
		siteSearchBtn.button("enable").button("refresh");
	});
}

function highlightSearchKeyword(str, keywords) {
	$.each(keywords, function(i, keyword) {
		str = str.replace(new RegExp(keyword.replace(/./, "\\$&"), "ig"), '<span class="searchKeyHighlight">$&</span>').replace(/(<br \/>\r\n)*$/, "");
	});
	return str;
}

function onPageScroll() {
	var scrollDiv = tom.$APC("scrollDiv");
	if (scrollDiv.length == 0) {
		return;
	}
	var page = tom.$AP();
	var scope = tom.$scope(page);
	var nowScrollTop = $(window).scrollTop();
	var scrollDelta = Math.abs(nowScrollTop - scope.val.scrollDivControlLastScrollTop);
	if (scrollDelta > 20) {
		scrollDiv.show();
	}
	scope.val.scrollDivControlLastScrollTime = new Date();
	scope.val.scrollDivControlLastScrollTop = nowScrollTop;
}

function guidToSSL(page) {
	if (document.location.protocol.indexOf("https") == 0)
		return;
	var sslSite = page.attr("data-ssl-site");
	if (!sslSite)
		return;
	if ($.cookie("tomomiRemindGuideSSL"))
		return;

	var scope = tom.$scope(page);
	scope.wait(100).done(
			function() {
				confirmDialog("<h3 style='text-align:center'>セキュアURLへ変更のお願い--Tomomi</h3>いつもご利用いただきありがとうございます<br>SSL暗号で通信が保護される新URL'https:" + sslSite
						+ "/'始まりへ移行をお願いします。<br>下記ボタン[OK]で新URLへジャンプします。<br>ブックマークの更新等もお願いいたします。<br><div style='color:red'>【重要】アカウントは引き継がれますが再ログインが必要となります</span>",
						function() {
							scope.wait(100).done(function() {
								location.href = "https://" + sslSite + "/" + location.href.replace(/^http:\/\/.*?\/(.*)$/, "$1");
							});
						});
			});
	$.cookie("tomomiRemindGuideSSL", "true", {
		expires : 2
	});
}