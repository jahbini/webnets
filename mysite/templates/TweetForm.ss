<form $FormAttributes>
	<% if Message %>
	<p id="{$FormName}_error" class="message $MessageType">$Message</p>
	<% else %>
	<p id="{$FormName}_error" class="message $MessageType" style="display: none"></p>
	<% end_if %>
	
	<fieldset>
		<legend>$Legend</legend>
		<% control Fields %>
			<div id="$Name" class="field $Type $extraClass">
				<% if Title %><label class="left" for="$id">$Title</label><% end_if %>
				<% if RightTitle %><label class="right" for="$id">$RightTitle</label><% end_if %>
				<div class="formColumn">
					$Field
				</div>
				<% if Message %><span class="message $MessageType">$Message</span><% end_if %>
			</div>
		<% end_control %>
	</fieldset>

	<% if Actions %>
	<div class="Actions">
		<% control Actions %>
			$Field
		<% end_control %>
	</div>
	<% end_if %>
</form>
