<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Hot In $Geo</title>

    <style type="text/css"> 
        .header  { background: #e0e0ff; color: gray50; border-bottom: solid 1px blue; margin: 0 0 5px 0; padding: 2px; }
        .footer  { background: #e0e0ff; color: gray50; border-top: solid 1px blue; margin: 10px 0 0 0; }
        .company { font-weight: bold; }
        hr       { clear: both; border:solid; border-width:1px; border-bottom-color:#007300; border-top-color:#ffffff; border-left-color:#ffffff; border-right-color:#ffffff;}
        .top-news img { float: left; margin-right: 5px; }
        .top-news h3, .news h3 { font-size: large; font-weight: bold; }
        .accesskey { text-decoration: underline; }
        a { text-decoration: none; }
        .validation { margin-top: 10px; }

        .product img { float: left; margin-right: 5px; }
        .product h3, .news h3 { font-size: large; font-weight: bold; }
    </style>
  </head>
  <body>
<% if false %>
    <!-- 
      All pages start with a common header and navigation bar so that users can
      navigate back to earlier pages without needing to scroll right down to the
      bottom of the page. Note that we still provide styling using XHTML
      elements where appropriate rather than by using the equivalent CSS styles. 
      This is so that the pages look their beston devices that do not support 
      CSS.
    -->
<% end_if %>
    <div class="header">
      <img src="$images/hnlteacup.jpg" width="112" height="84" alt="TweeParty logo" /><br />
<% if false %>
      <small>$PageIdentifier</small>
<% end_if %>
      <p>TweeParty.com presents the hot buzz about places and events near $Geo</p>
    </div>
<% if false %>
    <!-- 
      The page content is sandwiched between the common header and footer. In 
      this case, our content is a set of links to sections. Because the content
      of this page is static, it is sensible to provide access keys so that 
      users can access links immediately. Each section is labelled by the key
      number corresponding to its access key.
    -->
<% end_if %>
    <div class="content">
    
<% if Categories %>
<% control Categories %>
        <span class="accesskey">$Ord</span> <a href="$Top.Link(detail)/$ID" accesskey="$Pos">$userKey</a><br />
<% end_control %>
<% end_if %>
<% if RelayQueries %>
<hr />
<% control RelayQueries %>
<% if published %>
        <span class="accesskey">$Ord</span> $prettyDatetime(published): @$author_name says: $forTemplate<br />
<% else %>
        <span class="accesskey">$Ord</span> @$author_name says: $forTemplate<hr />
<% end_if %>
<% end_control %>
<% end_if %>

        <br />
        Add to the buzz -- tweet out your message with #$sponsor and visit our Web Site $GeoShort<!-- -->.tweeparty.com.
	$GeoShort<!-- -->.tweeparty.mobi is a service of tweeparty.com<br />
        
   </div>
<% if false %>
   
   <!-- 
     The common footer provides the copyright statement for your company and a
     second copy of the navigation bar so that users don't need to scroll right
     back to the top.
   -->
<% end_if %>
    <div class="footer">
       <small>
         &copy; The Great Dodecahedron, Inc. All rights reserved.<br />
       </small>
    </div>
<% if false %>
    
    <!-- 
      The following div element provides an easy way for you to check this web
      page against the XHTML-MP and CSS validators. Once complete, you may
      prefer to remove this comment and the div.
    -->
    <div class="validation">
      <a href="http://validator.w3.org/check?uri=referer">
        <img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML-MP" height="31" width="88" />
      </a>
      <a href="http://jigsaw.w3.org/css-validator/check/referer">
        <img src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS" width="88" height="31" />
      </a>
    </div>
<% end_if %>
  </body>
</html>
