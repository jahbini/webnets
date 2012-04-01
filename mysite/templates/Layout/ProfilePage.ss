<% if notValid %>
<h1> Complete your membership with a  valid password</h1>
<% else %>
<h1> Update your user information here </h1>
<% end_if %>
<div id="userProfileForm"><p class="headerID" >div userProfileForm</p><p>This information is how we know you.  Your user name and contact information are completely separate from your information with twitter.</p> $form</div>
<div id="VisualsForm"><p class="headerID" >div VisualsForm</p><p>Your front page will have several vertical sticks of tweets and users. These are grouped into panels.  Each of these panels will constantly refresh to get you the latest tweets, and keep you involved in the conversation.</p> <h3>Create your layout here.</h3>
<% control Profile.Modes %>
<h4>Edit mode <a href="$Top.Link?mode=$Use" >$Use</a></h4>
<% end_control %>
<br>
<h3>this is your Layout for $currentMode.Use</h3>
$visualFormEditor</div>
<div id='penNames' >
<p class="headerID" >div penNames</p>
<p>This is the place where you allow us to get and send your tweets.  You click on the link to get a new pen-name (that is really your twitter name) and we will send you to twitter.com, where you will be able to  authorize your current twitter name, or even create a new twitter name.  when you authorize twitter to be your go-between, you will come back to this page!  It is simple and safe.  You can have any number of pen-name accounts, many people have a personal pen-name, a business pen-name and a very personal pen name.  We can handle all of that!</p>
<% if Profile.PenNames %>
$newPenNameForm
<p>These are the pen-names you have authorized to  use with twitter</p>
<ol>
<% control Profile.PenNames %>
<li> $Title </li>
<% end_control %>
</ol>
<% else %>
You do not yet have a pen-name: $newPenNameForm
<% end_if %>
</div>

<div id="QueryRegion">
<p class="headerID" >div QueryRegion</p>
<div id="newQuery"> $newQueryForm </div>
<% control Profile.Queries %>
Query
<% end_control %>
	<div>
<% if WatchList %>
	<h2>These are the Queries you are currently watching</h2>
	<% control WatchList %>
		<h4>$Title - $TweetType  Query : $query
		<a href="$Top.URLSegment/editWatch/$ID"> Here to edit</a></h4>
	<% end_control %>
<% else %>
<h2> You are not watching any Queries yet </h2>
<% end_if %>
</div>
