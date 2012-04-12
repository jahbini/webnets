<div id="modes">
<h1>Display definitions for $Organizer ( a Twitter pen name of $Organizer.Profile.Name )</h1>
<h2>OrganizerLayoutPage.ss</h2>
<% if DisplayForm() %>
<div id='newDef'>
$DisplayForm()
<a href='{$link}?Organizer=$ID'> Return to Pane/Query display for $Organizer.screen_name </a>
</div>
<% else %>
$forceMode(attract)
$forceMode(loggedIn)
<% control Organizer.Modes %>
<h2>Mode ID = $ID used for $Use (attract a user or when user is logged in - if you allow that) <a href='{$Top.link}deleteMode?Mode=$ID'>Delete</a></h2>
$forcePane($ID)
<ul>
<% control Panes %>
<% if editThisPane($ID) %>
<li><div>$editPaneForm</div></li>
<% else %>
<li>Pane: Used as Pane.userKey= $userKey, Pane.ID=$ID, Pane.width=$width
<a href='{$Top.link}editPaneInfo?Pane=$ID' >Alter Pane information</a>
<a href='{$Top.link}deletePaneInfo?Pane=$ID' >Delete this Pane and all queries under it</a>
</li>
<% end_if %>
<ul>
<% control Queries %>
<li>$ID , $Title , $ClassName
<a href='{$Top.link}deleteQuery/Query=$ID' >Delete this  Query</a></li>
</li>
<% end_control %>
<li>:::::
<a href='{$Top.link}newQuery?Pane=$ID' >Create a new Query in this pane</a></li>
</ul>
<% end_control %>
::::::<a href='{$Top.link}newPane?Mode=$ID' >Create an adjacent pane in $Use mode</a>
<% end_control %>
<% end_if %>
