<div class="cms-tree-view-modes">
	<span><% _t("TreeTools.DisplayLabel","Display:") %></span>
	<% if CanOrganiseSitetree %> 
	<div class="checkboxAboveTree">
		<input type="radio" name="view-mode" class="view-mode" value="draggable" id="view-mode-draggable" />
		<label for="view-mode-draggable"><% _t("ENABLEDRAGGING","Drag'n'drop") %></label>
	</div>
	<% end_if %>
	<div>
		<input type="radio" name="view-mode" class="view-mode" value="multiselect" id="view-mode-multiselect" />
		<label for="view-mode-multiselect"><% _t("MULTISELECT","Multi-selection") %></label>
	</div>
</div>

<div class="cms-actions-row">
	<a class="ss-ui-button ss-ui-action-constructive cms-panel-link" data-icon="add" href="$AddLink"><% _t('CMSMain.AddNewButton', 'Add new') %></a>
</div>

<div class="cms-content-batchactions">
	$BatchActionsForm
</div>