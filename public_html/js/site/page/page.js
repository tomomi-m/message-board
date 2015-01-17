$(document).on("pagecontainershow.tomomi", pageSetup);

$(document).on("pagecontainerbeforeshow.tomomi", pageDestroy);

$(document).on("popupbeforeposition.tomomi", ".popupImageDiv", function() {
	var maxHeight = $(window).height() - 60 + "px";
	$("img", $(this)).css("max-height", maxHeight);
});

$(document).on("popupafteropen.tomomi", ".popupLoginDiv", function() {
	$("input", $(this)).first().focus();
});

$(document).on("click.tomomi", "[name ='pageContentsDiv'] img", pageImageClick);

function pageSetup(event, ui) {
	var page = tom.$AP();
	var scope = new Scope();
	tom.$scope(page, scope);

	headerInit(page);

	var messagesUl = tom.$OC(page, "messagesUl");
	if (messagesUl.length) {
		messagesUl.listview({
			autodividers : true,
			autodividersSelector : function(li) {
				var out = li.attr("user-data-posted-date");
				return out;
			}
		});

		if (!messagesUl.attr("user-data-once-refreshed")) {
			tom.$OC(page, "messageGetOlderA").hide();
			tom.$OC(messagesUl, "messagePollProgressDiv").text("⇔");
			var query = function() {
				scope.simpleAjax(document.URL + "/get-latest-xmessages", {}).done(function(result, textStatus, xhr) {
					$("li", messagesUl).remove();
					chatAppendmessagesUl(page, result, "top", true);
					chatPollMessage(page);
				}).fail(function() {
					var li = $("li", messagesUl);
					li.text(li.text() + ".fail retry.");
					scope.wait(10000).done(query);
				})
			};
			query();
		} else {
			chatPollMessage(page);
		}
	}

	var messageInputDiv = tom.$OC(page, "messageInputDiv");
	if (messageInputDiv.length) {
		var chatExpandInputDetailBtn = tom.$OC(messageInputDiv, "chatExpandInputDetailBtn")[0]
		chatExpandInputDetail(chatExpandInputDetailBtn);
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

	if (messageInputDiv.length) {
		var chatContractInputDetailBtn = tom.$OC(messageInputDiv, "chatContractInputDetailBtn")[0]
		chatContractInputDetail(chatContractInputDetailBtn);

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
}

function pageDestroy(event, ui) {
	var page = ui.prevPage;
	var scope = tom.$scope(page);
	if (scope) {
		scope.destroy();
		tom.$scope(page, null);
	}
}

function headerInit(page) {
	var popupMenuDiv = tom.$OC(page, "popupMenu");
	if (popupMenuDiv.length > 0) {
		popupMenuDiv.removeClass("loged-in not-loged-in query-loged-in");
		popupMenuDiv.addClass("query-loged-in");
		logedInUserNameP = tom.$OC(page, "logedInUserNameP");
		logedInUserNameP.hide();
		var scope = tom.$scope(page);
		var queryLogedIn = function() {
			scope.simpleSyncAjax(getSitegUrl() + "/is-user-loged-in", {}).done(function(result, textStatus, xhr) {
				popupMenuDiv.removeClass("query-loged-in");
				if (result.userName) {
					popupMenuDiv.addClass("loged-in");
					logedInUserNameP.text(result.userName);
					logedInUserNameP.show();
				} else {
					popupMenuDiv.addClass("not-loged-in");
				}
			}).fail(function(result, textStatus, xhr) {
				scope.wait(5000).done(queryLogedIn);
			});
		};
		queryLogedIn();
	}
}

function chatAppendmessagesUl(page, result, direction, doControlGetOlderA) {
	if (direction == "bottom")
		result.messages.reverse();
	var authedUserName = result.authedUserName;
	if (authedUserName)
		authedUserName = authedUserName.toLowerCase();
	var messagesUl = tom.$OC(page, "messagesUl");
	$.each(result.messages, function(i, val) {
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
		var divCCCtimeName = $("<div style='padding-right:0em;'/>").append($.format.date(val.updated_at, "HH:mm")).append("<br/>").append(val.userName);
		var faceImg;
		if (val.imgFace)
			faceImg = $("<img style='margin-right:1em;'/>").attr("src", val.imgFace);
		var emotionImg;
		if (val.imgEmotion) {
			emotionImg = $("<img/>").attr("src", val.imgEmotion);
			if (faceImg) {
				emotionImg.css({
					position : "absolute",
					bottom : 0,
					right: 0,
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
		divC.append(val.message);
		divR.append(divC);

		divT.append(divR);
		li.append(divT);
		if (val.images) {
			divC = $("<div class='shadowImages' />").html(val.images);
			$("img", divC).on("click", chatImageClick);
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

	tom.$OC(messagesUl, "messagePollProgressDiv").text("at "+$.format.date(new Date(), "HH:mm:ss"));

	if (doControlGetOlderA) {
		var messageGetOlderA = tom.$OC(page, "messageGetOlderA");
		if (result.noMoreMessages) {
			messageGetOlderA.hide();
		} else {
			messageGetOlderA.show();
		}
	}
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
	var scope = tom.$scope(page);
	var query = function() {
		scope.simpleAjax(document.URL + "/get-older-messages", postData).done(function(result, textStatus, xhr) {
			img.attr("src", img.attr("data-img-bk"));
			img.removeAttr("data-img-bk");
			messageGetOlderA.removeClass("ui-disabled");
			chatAppendmessagesUl(page, result, 'bottom', true);
		}).fail(function() {
			scope.wait(10000).done(query);
		})
	};
	query();
}

function chatPollMessage(page) {
	var scope = tom.$scope(page);
	if (scope == null) return; // page destroyed;
	var messagesUl = tom.$OC(page, "messagesUl");
	var waitTime = 10000;
	//tom.$OC(messagesUl, "messagePollProgressDiv").text("z".repeat(waitTime / 2000));
	scope.wait(waitTime, "chatPollMessage").done(function() {
		var lastMessageLi = messagesUl.children(":eq(1)");
		var postData = {};
		if (lastMessageLi.length > 0) {
			postData["lastMessageId"] = lastMessageLi.attr("user-data-posted-id");
		}
		tom.$OC(messagesUl, "messagePollProgressDiv").text("⇔");
		scope.simpleAjax(document.URL.replace(/#.*$/, '') + "/get-latest-messages", postData).done(function(result, textStatus, xhr) {
			chatAppendmessagesUl(page, result, "top", false)
		}).fail(function() {
			tom.$OC(messagesUl, "messagePollProgressDiv").text("fail.retry...");
		}).always(function() {
			chatPollMessage(page);
		})
	}).progress(function(remainTime) {
		//tom.$OC(messagesUl, "messagePollProgressDiv").text("z".repeat(remainTime / 2000));
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
		lastMessageId : lastMessageLi.attr("user-data-posted-id"),
	}
	messageSubmitBtn.val("送中..").button("refresh");
	var scope = tom.$scope(page);
	scope.removeWaitByKey("chatPollMessage");
	tom.$OC(page, "messagePollProgressDiv").text("⇒");
	scope.simpleAjax(document.URL + "/add-message", postData).done(function(result, textStatus, xhr) {
		if (xhr.status == "202" && result.r == "are you human?") {
			messageSubmitBtn.val("認証要").button("refresh");
			loginPopup(messageSubmitBtn[0], function() {
				postMessage(self);
			});
		} else {
			messageTxt.val("");
			messageTxt.textinput("refresh");
			chatAttachImagesDiv.html("");
			messageSubmitBtn.val("送信").button("refresh");
			chatAppendmessagesUl(page, result, "top", false)
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
			pageEditSaveBtn.val("更新失敗(;; " + textStatus + ":" + result.statusText);
			pageEditSaveBtn.button("disable").button("refresh");
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
	appendImageToDiv(event, 120);
}

function chatExpandInputDetail(self) {
	self = $(self);
	self.hide();
	self.next().show();
	var page = tom.$AP();
	tom.$OC(page, "chatInputDetailSection").show();
}

function chatContractInputDetail(self) {
	self = $(self);
	self.hide();
	self.prev().show();
	var page = tom.$AP();
	tom.$OC(page, "chatInputDetailSection").hide();
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

function appendImageToDiv(event, prefferedSize, callback) {
	var fileInp = event.target;
	var imagesDiv = $(fileInp).next(":first");
	$
			.each(
					fileInp.files,
					function(idx, fileInfo) {
						if (/^image\//.test(fileInfo.type)) {
							$
									.when(readFileIntoDataUrl(fileInfo))
									.done(
											function(dataUrl) {
												var image = $("<img>").attr("src", dataUrl);
												image.hide();
												var table = $('<table border="0" style="display:inline-block"><tr><td><a name="moveImageBtn" data-role="button" data-icon="arrow-left" onclick="moveImageInDiv(event)">&nbsp;</a></td></tr><tr><td name="imageTd"></td></tr><tr><td><a name="delImageBtn" data-role="button" onclick="deleteImageFromDiv(event)">削除</a></td></tr></table>');
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
											}).fail(function(e) {
										pageAlert(e);
									});
						}
					});
	$(fileInp).replaceWith($(fileInp).clone(true));// reset file input
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
				divDesc.attr("style", "margin-left:1em; font-weight:normal; font-size: small;");
				divDesc.append(val.userName + " at " + val.updated_at + " " + val.message);
				a.append(divDesc);
				li.append(a);
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
	captchaImgDiv.append($("<img style='padding:0; margin:0' width='220' height='50'>").attr("src", "/captcha?" + myuuid()));
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
			scope.wait(0).done(scope.vals.loginCallback);
			headerInit(page);
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
