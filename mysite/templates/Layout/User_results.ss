<div id="JAH_users" class="typography">
<h2>$Users.totalSize Users are selected as $Tag</h2>
	      <% control Users %>
<div class="responder">
<% include TweetUser %>
<hr />
<% if Last3 %>
<div class="tweetsOf TweetUser__$ID">
<% control Last3 %>
<% include Tweet %>
<hr class="clear">
</div>
<% end_control %>
</div>
<% end_if %>
<hr class="clear">
</div>
	      <% end_control %>

          <% if Users.MoreThanOnePage %>
               <% if Users.NotFirstPage %> <a class="prev" href="$Users.PrevLink">Prev</a> <% end_if %>
           <% control Users.PaginationSummary(4) %>
                       <% if CurrentBool %> $PageNum <% else %>
                               <% if Link %> <a href="$Link">$PageNum</a> <% else %> ...  <% end_if %>
                       <% end_if %>
               <% end_control %>
               <% if Users.NotLastPage %> <a class="next" href="$Users.NextLink">Next</a> <% end_if %>
	 <% end_if %>
</div>

