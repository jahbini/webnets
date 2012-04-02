<div class="typography">

$ProfileLoginForm
<!--
<a href="http://www.dreamhost.com/donate.cgi?id=11822"><img border="0" alt="Donate towards my web hosting bill!" src="https://secure.newdream.net/donate4.gif" /></a>
-->
</div>
		$SearchForm
		<h1 class="papyrus">WeAllTwee</h1><% if RequestedTweet %><p>$RequestedTweet</p><% else %>
	    	<p>A fun place to share the smallest of small talk -- the leavening of life</p>
	    	<p>Twitter is a social experience.  It's a party. Bring yourself, talk about important or meaningless things. 
	    	It is all good in this tea party of life.  We are just beginning, and hope you can join the chat.</p>
<div style="margin: 5px 0 8px 50px"><p>I see friends shaking hands, saying &#147;how do you do&#148; -- They&#039;re really saying <em>&#147;I Love You&#148; -- What a Wonderful World.</em></p></div>
<% end_if %>
		</div>
		<div id="Navigation"> <% include Navigation %> </div>
	  	<div class="clear"><!-- --></div>
	<div class="tagbox hashTags left" ><ol><% control TagList(HashTag) %>
		<li><a class="linkTag tag_$ID tagcloud" title="tag $forTemplate has $NumTweets tweet(s)" href="$Top.RelativeLink(tag)/$forUrl">$Me</a></li>
	<% end_control %></ol></div>
	<div class="tagbox userTags center" ><ol><% control TagList(UserTag) %>
		<li><a class="linkTag tag_$ID tagcloud" title="tag $forTemplate has $NumTweets tweet(s)" href="$Top.RelativeLink(tag)/$forUrl">$Me</a></li>
	<% end_control %></ol></div>
	<div class="tagbox simpleTags right" ><ol><% control TagList(Tag) %>
	<li><a class="linkTag tag_$ID tagcloud" title="tag $forTemplate has $NumTweets tweet(s)" href="$Top.RelativeLink(tag)/$forUrl">$Me</a></li>
	<% end_control %></ol></div>
	<% if links %>
	<div id='links'>
	<% control links %>
	<p> <a href="$Link" class="NeedsPenName" > $Title </a><span class="hidden">$Title</span></p>
	<% end_control %>
	</div>
	<% end_if %>
	<div id='existingQuery'>
	$existingQueryForm
	</div>
	<% if HasPerm(QUERY_WATCHER) %>
	<% else %>
	<h1><em>Sign up to join the party -- get social and chat awhile</em></h1>
	<% end_if %>

		<h2>$Title</h2>
		$Content
		$Form
		$PageComments
	<% if Menu(2) %>
		</div>
	<% end_if %>
</div>

	
	
	
