URL Segment=$URLSegment, BaseHref=$BaseHref
<% if $notYetValid() %>
<h1> 2 Complete your membership with a  valid password</h1>
<% else %>
<h1> Update your user information here </h1>
<% end_if %>
<div class="clear">
<div id="userProfileForm"><p class="headerID" >
<p>This information is how we know you.  Your user name and contact information are completely separate from your information with twitter.</p>
$form
</div>
<div id='penNames' >
<p>This is the place where you allow us to get and send your tweets.  You click on the link to get a new pen-name (that is really your twitter name) and we will send you to twitter.com, where you will be able to  authorize your current twitter name, or even create a new twitter name.  when you authorize twitter to be your go-between, you will come back to this page!  It is simple and safe.  You can have any number of pen-name accounts, many people have a personal pen-name, a business pen-name and a very personal pen name.  We can handle all of that!</p>

<% if Profile.SubDomains %>
<p> These are the Organizations you have </p>
<ol>
<% control Profile.SubDomains %>
<li>{$Title} $linkToSubDomain  </em> <% if Organizer %> Organized by $Organizer.Title <a href="{$Top.Link}deleteOrganizer?Organizer=Organizer.ID"> delete $Organizer.Name</a> <% else %> No Current Organizer!  <%end_if %>
</li>
<% end_control %>
</ol>
<% end_if %>
<% if Profile.PenNames %>
<p>These are the pen-names you have authorized to  use with twitter</p>
<ol>
<% control Profile.PenNames %>
<li> $Title
<% if $ClassName==Organizer %>
- Organizer for SubDomain $SubDomain
<a href='$linkToSubDomain/Organizer?Organizer=$ID' > edit </a>
<% end_if %>
</li>
<% end_control %>
</ol>
<% else %>
<p>You do not yet have a pen-name, and are not able to fully use our system.</p>
<% end_if %>
<p>$newPenNameForm</p>
</div>
</div>
<% if $notYetValid() %>
<% else %>
<% if Profile.PenNames %>
<% if Profile.SubDomains %>
<h2> Re-assign SubDomain / Organizer association</h2>
$OrganizerSubDomainForm()
<% end_if %>
<% end_if %>

<% end_if %>

