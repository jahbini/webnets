<% include SideBar %>
$ProfileLoginForm
<div class="content-container">	
<div class="typography">
	<article>
		<h1>$Title</h1>
		<div class="content">$Content</div>
<% if SubDomain %>
		<div class="content">$SubDomain.Content</div>
<% end_if %>
<div id="result"> </div>
<div id="tweetlist" class="hidden" > </div>
	</article>
		$Form
		$PageComments
</div>
<h3>History</h3>
<div id="slider" ></div>
<h3>Latest Messages</h3>
$Queries
<% include TweetBox %>		
</div>

	
	
	
