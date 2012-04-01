<div id="QueryRegion">
hello
<p class="headerID" >div QueryRegion</p>
<div id="newQuery"> $newQueryForm </div>
<% control Profile.Queries %>
Query
<% end_control %>
	<div>
<% if Queries %>
	<h2>These are the Queries you are currently watching</h2>
<ul>
	<% control Queries %>
<li>
		<h4><a href="$Top.URLSegment/edit/$ID">edit $Title</a></h4>
		<h3>$query</h3>
		<h2>$requestString</h2>
		<p><a href="$Top.URLSegment/removeWatch/$ID">delete $Title</a> </p>
</li>
	<% end_control %>
</ul>
<% else %>
<h2> You are not watching any Queries yet </h2>
<% end_if %>
</div>
