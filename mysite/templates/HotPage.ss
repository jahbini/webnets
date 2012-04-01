<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >

  <head>
		<% base_tag %>
		<title>$Title &raquo; Twee Party -- $myHostName </title>
		$MetaTags(false)
		<link rel="shortcut icon" href="/favicon.ico" />
		
		<% require themedCSS(layout) %> 
		<% require themedCSS(typography) %> 
		<% require themedCSS(form) %> 
		
		<!--[if IE 6]>
			<style type="text/css">
			 @import url(themes/blackcandy/css/ie6.css);
			</style> 
		<![endif]-->
		
		<!--[if IE 7]>
			<style type="text/css">
			 @import url(themes/blackcandy/css/ie7.css);
			</style> 
		<![endif]-->
	</head>
<body>
<div id="BgContainer">
	<div id="Container">
		<div id="Header">
<div id="topright">
<a href="http://www.dreamhost.com/donate.cgi?id=11822"><img border="0" alt="Donate towards my web hosting bill!" src="https://secure.newdream.net/donate4.gif" /></a>
</div>
		<h1 class="papyrus">WeAllTwee presents Hot in HNL</h1>
		</div>
	  	<div class="clear"><!-- --></div>
	<% if HasPerm(REGULAR_USER) %>
	<% else %>
	<h1><em>Sign up to join the party -- get social and chat awhile</em></h1>
	<% end_if %>
<div id="Layout"> $Layout </div>
<div class="clear"><!--  Clear for footer --></div>
</div>
<div id="Footer"><% include Footer %></div> 
</div>
</body>
</html>
