<div class="clear"></div>
<div class='Tweeter'>
<div class='userControl'>$followFactor
<% if fullInfo %>
<br />Initial follow $prettyDate(FriendshipExtendedOn)
<br />joined: $prettyDate(created_at)
<br />followers:$followers_count
<br />follows: $friends_count
<br />total tweets:$statuses_count
<% else %>
<br />No info on $screen_name
<% end_if %>
</div>
<div class='userInfo'><a href="$Top.RelativeLink(author)/$screen_name">Tweets from $screen_name</a>- $name -<a href="$url" target="_blank">$url</a>-- <% if location %> where: $location,<% end_if %><% if description %><br /> $description,<% end_if %>
</div>
</div>
