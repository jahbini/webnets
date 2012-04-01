<tr id="query__$ID" class="query__$ID" ><td>
<a class="query_drop query__$ID" href="$Top.RelativeLink(dropQuery)?Query=$ID">Drop Query</a>
|<a class="query_init query__$ID" href="$Top.RelativeLink(initQuery)?Query=$ID">Reinit Query</a>
$Title</td>
	<td>$TweetType</td><td>$query</td>
	<td>$lowest</td>
	<td>$highest</td></tr>
<% if myGaps %>
<tr class="query_drop query__$ID"><td colspan=5> <table>
<% control myGaps %>
<% include Gap %>
<% end_control %>
</table></td> </tr>
 <% end_if %>
