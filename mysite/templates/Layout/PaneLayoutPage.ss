<div id="modes">
<h1>Display definitions for $PenName.screen_name ( a Twitter pen name of $PenName.Profile.Name )</h1>
<% if DisplayForm() %>
<div id='newDef'>
$DisplayForm()
<a href='{$link}allModes/$PenName.ID'> Return to Pane/Query display for $PenName.screen_name </a>
</div>
<% else %>
$forceMode(attract)
$forceMode(loggedIn)
<% control PenName.Modes %>
<h2>used for $Use (attract a user or when user is logged in - if you allow that) <a href='{$Top.link}deleteMode/$ID'>Delete</a></h2>
$forcePane($ID)
<ul>
<% control Panes %>
<% if editThisPane($ID) %>
<li><div>$editPaneForm</div></li>
<% else %>
<li>Pane: Used as Pane.userKey= $userKey, Pane.ID=$ID, Pane.width=$width
<a href='{$Top.link}editPaneInfo/$ID'> Alter Pane information</a>
<a href='{$Top.link}deletePaneInfo/$ID'> Delete this Pane and all queries under it</a>
</li>
<% end_if %>
<ul>
<% control Queries %>
<li>$ID , $Title , $ClassName
<a href='{$Top.link}allModes/$ID/editQuery'>Edit this  Query</a></li>
</li>
<% end_control %>
<li>:::::
<a href='{$Top.link}allModes/$ID/newQuery'>Create a new Query in this pane</a></li>
</ul>
<% end_control %>
::::::<a href='{$Top.link}allModes/$ID/newPane'>Create an adjacent pane in $Use mode</a>
<% end_control %>
<% end_if %>
