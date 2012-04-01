<div id="JAH_tags" class="typography">
<% include TweetBox %>
	<% if Tag %>
<h2>$Tweeties.totalSize Tweets are Tagged with $Tag</h2>

          <% if Tweeties.MoreThanOnePage %>
               <% if Tweeties.NotFirstPage %> <a class="prev" href="$Tweeties.PrevLink">Prev</a> <% end_if %>
           <% control Tweeties.PaginationSummary(4) %>
                       <% if CurrentBool %> $PageNum <% else %>
                               <% if Link %> <a href="$Link">$PageNum</a> <% else %> ...  <% end_if %>
                       <% end_if %>
               <% end_control %>
               <% if Tweeties.NotLastPage %> <a class="next" href="$Tweeties.NextLink">Next</a> <% end_if %>
	 <% end_if %>

	      <% control Tweeties %>
<hr class="clear" />
<div class="responder">
<% include Tweet %>
<% control TweetUser %>
<% include TweetUser %>
<% end_control %>
</div>

<hr class="clear" />
</div>
	      <% end_control %>
	  <% else %>
	    <p>Sorry, The tag $Tag is not associated with any Tweets in the local database, You may load the database with a search.</p>
	  <% end_if %>

          <% if Tweeties.MoreThanOnePage %>
               <% if Tweeties.NotFirstPage %> <a class="prev" href="$Tweeties.PrevLink">Prev</a> <% end_if %>
           <% control Tweeties.PaginationSummary(4) %>
                       <% if CurrentBool %> $PageNum <% else %>
                               <% if Link %> <a href="$Link">$PageNum</a> <% else %> ...  <% end_if %>
                       <% end_if %>
               <% end_control %>
               <% if Tweeties.NotLastPage %> <a class="next" href="$Tweeties.NextLink">Next</a> <% end_if %>
	 <% end_if %>

</div>

