var tomomi_ssl = {
	sync : function(baseUrl, cook) {
		var xmlHttp;

		if (window.XMLHttpRequest) {
			xmlHttp = new XMLHttpRequest();
		} else {
			if (window.ActiveXObject) {
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			} else {
				xmlHttp = null;
			}
		}
		xmlHttp.open("POST", baseUrl + "/ping", false);
		xmlHttp.setRequestHeader("Cookie", cook);
		xmlHttp.send(null);
	}
}