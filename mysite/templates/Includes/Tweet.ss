<div class="one_tweet tweet_id_$ID screen_name__$author_name"><div class="tagList"><% if tagged %><% control SimpleTags %><span
	   class="TagList tag_$ID"><a class="linkTag tag_$ID tagCloud" title="tag $forTemplate has $NumTweets tweet(s)"
					href="$Top.RelativeLink(tag)/$forUrl">$Me</a>
	<% if Classic %>
	<span class="isClassic tag_$ID" title="Tweets of this tag are Eternal" >&#X269a;</span>
	<% else %>
<% if HasPerm(ADMIN) %>
		:<a class="dropTag drop_$ID" title="drop tag $forTemplate from DataBase" 
			href="$Top.RelativeLink(dropTag)/$forUrl">&#x2620;</a>
		<a class="makeClassic classy_$ID" title="make all tweets of this tag Eternal" 
			href="$Top.RelativeLink(makeClassic)/$forUrl">&#x2624;</a>
<% end_if %>
	<% end_if %>
	</span><% end_control %><% end_if %></div>
<div class="tweetControl">
$prettyDatetime(published)-- 
<% if HasPerm(QUERY_WATCHER) %>
<span class="acton__$ID">
<a href=$Top.RelativeLink(actOn)/$ID class="actOn"><% if actedOn %>Acted On $actedOn<% else %>act on this<% end_if %></a>
</span>
<% else %>
 --
<% end_if %>
</div>
<div class="tweetContents <% if recipient_screen_name %>directTweet<% end_if %>">
$decoratedTitle  
</div>
