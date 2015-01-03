/* http://github.com/mindmup/bootstrap-wysiwyg */
/*global jQuery, $, FileReader*/
/*jslint browser:true*/
(function($) {
	'use strict';

	$.fn.cleanHtml = function() {
		var html = $(this).html();
		return html && html.replace(/(<br>|\s|<div><br><\/div>|&nbsp;)*$/, '');
	};
	$.fn.wysiwyg = function(userOptions) {
		var editor = this;
		var selectedRange;
		var options;
		var toolbarBtnSelector;
		var execCommand = function(commandWithArgs, valueArg) {
			var commandArr = commandWithArgs.split(' ');
			var command = commandArr.shift();
			var args = commandArr.join(' ') + (valueArg || '');
			switch (command) {
			case "autoresize":
				var heightMax = 270;
				var widthMax = 300;
				var images = $("img", editor);
				images.each(function(i, image) {
					autoResize(image, widthMax, heightMax)
				});
				break;
			case "autoresize2":
				var heightMax = 150;
				var widthMax = 150;
				var images = $("img", editor);
				images.each(function(i, image) {
					autoResize(image, widthMax, heightMax)
				});
				break;
			case "explode-toolbar":
				toolbar.find(".large-only").each(function() {
					$(this).show();
				});
				toolbar.find(".small-only").each(function() {
					$(this).hide();
				});
				break;
			case "implode-toolbar":
				toolbar.find(".large-only").each(function() {
					$(this).hide();
				});
				toolbar.find(".small-only").each(function() {
					$(this).show();
				});
				break;

			default:
				document.execCommand(command, 0, args);
			}
		};
		var getCurrentRange = function() {
			var sel = window.getSelection();
			if (sel.getRangeAt && sel.rangeCount) {
				return sel.getRangeAt(0);
			}
		};
		var saveSelection = function() {
			selectedRange = getCurrentRange();
		};
		var restoreSelection = function() {
			var selection = window.getSelection();
			if (selectedRange) {
				try {
					selection.removeAllRanges();
				} catch (ex) {
					document.body.createTextRange().select();
					document.selection.empty();
				}
				selection.addRange(selectedRange);
			}
		};
		var insertFiles = function(files) {
			editor.focus();
			$.each(files, function(idx, fileInfo) {
				if (/^image\//.test(fileInfo.type)) {
					$.when(readFileIntoDataUrl(fileInfo)).done(function(dataUrl) {
						execCommand('insertimage', dataUrl);
						setTimeout(function() {
							execCommand("autoresize");
						}, 100);
					}).fail(function(e) {
						options.fileUploadError("file-reader", e);
					});
				} else {
					options.fileUploadError("unsupported-file-type", fileInfo.type);
				}
			});
		};
		var insertThumbnail = function(fileInfo) {
			if (/^image\//.test(fileInfo.type)) {
				$.when(readFileIntoDataUrl(fileInfo)).done(function(dataUrl) {
					var thumbnaiDiv = thumbnaiMasterCtrl.parent().parent().prev();
					thumbnaiDiv.attr("src", dataUrl);
					thumbnaiDiv.attr("width", "80");
					thumbnaiDiv.attr("height", "80");
				}).fail(function(e) {
					options.fileUploadError("file-reader", e);
				});
			} else {
				options.fileUploadError("unsupported-file-type", fileInfo.type);
			}
		};
		var markSelection = function(input, selectionColor) {
			restoreSelection();
			if (document.queryCommandSupported('hiliteColor')) {
				document.execCommand('hiliteColor', 0, selectionColor || 'transparent');
			}
			saveSelection();
			input.data(options.selectionMarker, color);
		};
		var bindToolbar = function(toolbar, options) {
			toolbar.find(':button').click(function() {
				restoreSelection();
				editor.focus();
				execCommand($(this).data("wysiwyg-command"));
				saveSelection();
			});

			var fileInputs = toolbar.find('input[type=file][data-wysiwyg-command]');
			fileInputs.change(function() {
				restoreSelection();
				if (this.type === 'file' && this.files && this.files.length > 0) {
					insertFiles(this.files);
				}
				saveSelection();
				this.value = '';
			});
		};
		var initFileDrops = function() {
			editor.on('dragenter dragover', false).on('drop', function(e) {
				var dataTransfer = e.originalEvent.dataTransfer;
				e.stopPropagation();
				e.preventDefault();
				if (dataTransfer && dataTransfer.files && dataTransfer.files.length > 0) {
					insertFiles(dataTransfer.files);
				}
			});
		};
		var options = $.extend({}, $.fn.wysiwyg.defaults, userOptions);
		if (options.dragAndDropImages) {
			initFileDrops();
		}
		var activePage = $($.mobile.pageContainer.pagecontainer("getActivePage")[0]);
		var toolbar = $("[name=wysiwyg-toolbar]", activePage);
		bindToolbar(toolbar, options);
		editor.attr('contenteditable', true).on('mouseup keyup mouseout', function() {
			saveSelection();
		});
		$(window).bind(
				'touchend',
				function(e) {
					var isInside = (editor.is(e.target) || editor.has(e.target).length > 0), currentRange = getCurrentRange(), clear = currentRange
							&& (currentRange.startContainer === currentRange.endContainer && currentRange.startOffset === currentRange.endOffset);
					if (!clear || isInside) {
						saveSelection();
					}
				});

		var thumbnailFileInput = $('input[type=file].thumbnail-control', activePage);
		thumbnailFileInput.change(function() {
			if (this.type === 'file' && this.files && this.files.length > 0) {
				insertThumbnail(this.files[0]);
			}
			this.value = '';
		});
		var thumbnaiMasterCtrl = thumbnailFileInput.prev();
		thumbnailFileInput.css('opacity', 0).css('position', 'absolute').offset(thumbnaiMasterCtrl.offset()).width(thumbnaiMasterCtrl.outerWidth())
				.height(thumbnaiMasterCtrl.outerHeight());
		execCommand("implode-toolbar");
		return this;
	};
	$.fn.wysiwyg.defaults = {
		selectionMarker : 'edit-focus-marker',
		selectionColor : 'darkgrey',
		dragAndDropImages : true,
		fileUploadError : function(reason, detail) {
			console.log("File upload error", reason, detail);
		}
	};
}(window.jQuery));
