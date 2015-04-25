//var Scopes = function(id) {
//	this.scopes = {};
//	this.poolTime = 500;
//	this.spend(this);
//}
//
//Scopes.prototype = {
//	create: function(id) {
//		var scope = new Scope(id);
//		this.scopes[scope.id] = scope;
//		return scope;
//	},
//
//	del: function(scope) {
//		var id = (typeof scope == 'string')?scope:scope.id;
//		var scope = this.scopes[id];
//		scope.destroy();
//		delete this.scopes[id];
//	},
//
//	spend: function(self) {
//		$.each(self.scopes, function(i, scope) {
//			scope.spendTimeout(self.poolTime);
//		});
//		setTimeout(self.spend, self.poolTime, self);
//	},
//}

var Scope = function(id) {
	this.id = id ? id : myuuid();
	this.vals = {};
	this.waits = [];
	this.ajaxs = [];
	this.poolTime = 2000;
	this.timeid = -1;
	this.doAbort = false;
	this.spend(this);
}
Scope.prototype = {
	val : function() {
		return this.vals;
	},

	destroy : function() {
		this.doAbort = true;
		this.cancelWaits();
		this.cancelAjaxs();
	},

	spend : function(self) {
		self.timeid = -1;
		if (self.doAbort)
			return;
		self.spendWait(self.poolTime);
		if (self.doAbort)
			return;
		self.timeid = setTimeout(self.spend, self.poolTime, self);
	},

	spendWait : function(time) {
		if (this.doAbort)
			return;
		var waits = this.waits.concat();
		$.each(waits, function(i, timer) {
			if (this.doAbort)
				return;
			timer.spend(time);
		});
	},

	wait : function(time, cancelKey) {
		if (time > 0) {
			var timer = new Timer(time, this, cancelKey);
			this.waits.push(timer);
			return timer.promise;
		} else {
			var dfd = $.Deferred();
			setTimeout(function() {
				dfd.resolve();
			}, 10);
			return dfd.promise();
		}
	},

	removeWait : function(timer) {
		var i = $.inArray(timer, this.waits);
		if (i >= 0)
			this.waits.splice(i, 1);
	},

	removeWaitByKey : function(cancelKey) {
		for (var i = 0; i < this.waits.length; i++) {
			if (this.waits[i].cancelKey == cancelKey) {
				this.waits.splice(i, 1);
				break;
			}
		}
	},

	cancelWaits : function() {
		if (this.timeid >= 0)
			clearTimeout(this.timeid);
		var waits = this.waits.concat();
		$.each(waits, function(i, timer) {
			timer.abort();
		});
		this.waits = [];
	},

	ajax : function() {
		if (this.doAbort)
			return;
		var defaults = {
			type : "POST",
			dataType : 'json',
			timeout : 60000,
		};
		var args = [];
		for (var i = 0; i < arguments.length; i++) {
			args.push(arguments[i]);
		}
		data = $.extend({}, defaults, args[0]);
		var ajaxKey = data.ajaxKey;
		args[0] = data;
		var xhr = $.ajax.apply(window, args);
		this.ajaxs.push({
			ajaxKey : ajaxKey,
			xhr : xhr
		});
		var self = this;
		xhr.always(function() {
			self.removeAjax.apply(self, [ xhr ]);
		});
		return xhr;
	},

	simpleAjax : function(url, data) {
		return this.ajax({
			url : url,
			data : data
		});
	},

	simpleSyncAjax : function(url, data) {
		return this.ajax({
			url : url,
			data : data,
			async : false,
		});
	},

	removeAjax : function(xhr) {
		for (var i = 0; i < this.ajaxs.length; i++) {
			if (this.ajaxs[i].xhr === xhr)
				this.ajaxs.splice(i, 1);
		}
	},

	cancelAjaxs : function() {
		var ajaxs = this.ajaxs.concat();
		$.each(ajaxs, function(i, ajax) {
			ajax.xhr.abort();
		});
		this.ajaxs = [];
	},

	cancelAjaxsByKey : function(ajaxKey) {
		for (var i = 0; i < this.ajaxs.length; i++) {
			if (this.ajaxs[i].ajaxKey == key) {
				this.ajaxs[i].xhr.abort();
				this.ajaxs.splice(i, 1);
				break;
			}
		}
	}
}

var Timer = function(timeout, scope, cancelKey) {
	this.timeout = timeout;
	this.scope = scope;
	this.cancelKey = cancelKey;
	this.dfd = $.Deferred();
	this.promise = this.dfd.promise();
}
Timer.prototype = {
	spend : function(time) {
		if ((this.timeout -= time) <= 0) {
			this.scope.removeWait(this);
			this.dfd.resolve();
		} else {
			this.dfd.notify(this.timeout);
		}
	},
	abort : function() {
		this.dfd.reject();
	},
}

function myuuid() {
	var S4 = function() {
		return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
	}
	return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
};

function stringEndsWith(str, suffix) {
	return str.indexOf(suffix, str.length - suffix.length) !== -1;
}

function readFileIntoDataUrl(fileInfo) {
	var loader = $.Deferred(), fReader = new FileReader();
	fReader.onload = function(e) {
		loader.resolve(e.target.result);
	};
	fReader.onerror = loader.reject;
	fReader.onprogress = loader.notify;
	fReader.readAsDataURL(fileInfo);
	return loader.promise();
};

function autoResize(image, widthMax, heightMax) {
	image = $(image);
	var height = image.height();
	var width = image.width();
	image.removeAttr("height");
	image.removeAttr("width");
	image.css("height", "");
	image.css("width", "");
	var pureHeight = image.height();
	var pureWidth = image.width();
	var aspect = pureHeight / pureWidth;
	width = height * pureWidth / pureHeight;

	if (height > heightMax) {
		height = heightMax;
		width = height * pureWidth / pureHeight;
	}
	if (width > widthMax) {
		width = widthMax;
		height = width * pureHeight / pureWidth;
	}

	image.width(Math.floor(width));
	image.height(Math.floor(height));
}

$.widget("mobile.popup", $.mobile.popup, {
	_handleWindowResize : function() {
		if (this.options.followResize)
			this._superApply(arguments);
	},
});

var tom = {
	$OPC : function(origin, parentName, childName) {
		return this.$OC(this.$OP(origin, parentName), childName);
	},
	$OP : function(origin, parentName) {
		return $(origin).closest("[name='" + parentName + "']");
	},
	$OC : function(origin, childName) {
		return $("[name='" + childName + "']", origin);
	},
	$AP : function() {
		return $($.mobile.pageContainer.pagecontainer("getActivePage")[0]);
	},
	$APC : function(name) {
		return $("[name='" + name + "']", this.$AP());
	},
	$scope : function(page, scope) {
		return (arguments.length == 2) ? page.jqmData("tomomi", scope) : page.jqmData("tomomi");
	},
};

if (!String.prototype.repeat) {
	String.prototype.repeat = function(num) {
		for (var str = ""; (this.length * num) > str.length;)
			str += this;
		return str;
	}
};


function CreateSwfPlayer(title, src, divContents) {
	$(divContents).empty();
  var so = new SWFObject("/player-viral.swf", "movie", "500", "300", "8", "#336699");
  so.addParam("allowfullscreen", "true");
  so.addParam("allowscriptaccess", "always");
  so.addVariable("file",src);
  so.addVariable("title",title);
  so.addVariable("autostart","false");
  so.write(divContents);
}
