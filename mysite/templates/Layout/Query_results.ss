<div id="JAH_tags" class="typography">
	<% if Queries %>
<h2>The number of active queries is  $Queries.totalSize </h2>
	    <table id="queries">
<tr><td>Title</td> <td>TYPE</td>      <td>query sent to Twitter</td><td>Lowest gathered</td><td>Highest gathered</td></tr>
	      <% control Queries %>
<% include OneQuery %>
	      <% end_control %>
	    </table>
	  <% else %>
	    <p>Sorry, There are no queries active.</p>
	  <% end_if %>

          <% if Queries.MoreThanOnePage %>
               <% if Queries.NotFirstPage %> <a class="prev" href="$Queries.PrevLink">Prev</a> <% end_if %>
           <% control Queries.PaginationSummary(4) %>
                       <% if CurrentBool %> $PageNum <% else %>
                               <% if Link %> <a href="$Link">$PageNum</a> <% else %> ...  <% end_if %>
                       <% end_if %>
               <% end_control %>
               <% if Queries.NotLastPage %> <a class="next" href="$Queries.NextLink">Next</a> <% end_if %>
	 <% end_if %>
</div>
