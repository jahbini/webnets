<div class="typography">
<h2>Search results</h2>
	<% if Results %>
<h2>$Results.totalSize Tweets</h2>
	    <ul id="SearchResults">
	      <% control Results %>
	        <li>
<% include Tweet %>
<% control TweetUser %>
<% include TweetUser %>
<% end_control %>
<hr class="clear" />
</div>
	        </li>
	      <% end_control %>
	    </ul>
	  <% else %>
	    <p>Sorry, your search query did not return any results.</p>
	  <% end_if %>
          <% if Results.MoreThanOnePage %>
               <% if Results.NotFirstPage %>
                       <a class="prev" href="$Results.PrevLink">Prev</a>
               <% end_if %>
           <% control Results.PaginationSummary(4) %>
                       <% if CurrentBool %>
                               $PageNum
                       <% else %>
                               <% if Link %>
                                       <a href="$Link">$PageNum</a>
                               <% else %>
                                       ...
                               <% end_if %>
                       <% end_if %>
               <% end_control %>
               <% if Results.NotLastPage %>                                                 
                       <a class="next" href="$Results.NextLink">Next</a>                    
               <% end_if %>                                                                 
          <% end_if %>                                                                      
</div>
