<div name="scrollDiv" style="display:table; position:fixed; bottom:1.5em; right:0px;">
	<div style="table-row; margin-bottom:0.3em">
		<div onclick="$('body, html').animate({ scrollTop: 0 }, 500);" style="display:table-cell;background-color:rgba(200, 200, 200, 0.6); width:3em; height: 2em;border-radius:10px;">
			<div style="margin: 0.2em; border-left: 1.4em solid transparent;border-bottom: 0.7em solid;border-right: 1.4em solid transparent;"></div>
		</div>
		<div style="display:table-cell;width:0.5em">
		</div>
		<div onclick="$('body, html').animate({ scrollTop: $(document).height()-$(window).height()  }, 500);" style="display:table-cell;background-color:rgba(200, 200, 200, 0.6); width:3em; height: 2em;border-radius:10px;vertical-align:bottom;">
			<div style="margin: 0.2em; border-left: 1.4em solid transparent;border-top: 0.7em solid;border-right: 1.4em solid transparent;"></div>
		</div>
	</div>
</div>
