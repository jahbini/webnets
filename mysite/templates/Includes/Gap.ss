<tbody id="gapp$ID" class="gap__$ID"><tr class="gap__$ID"><td>
<a class="gap_drop" href="$Top.RelativeLink(dropGap)?Gap=$ID">DROP</a> , <a class="gap_reschedule" href="$Top.RelativeLink(rescheduleGap)?Gap=$ID">Reschedule</a>
$gapKind</td><td>$Created.Ago</td><td>$LastEdited.Ago<br />$TimeOffset</td><td>$bottom</td><td>$current</td><td>$topOfGap</td></tr>
<% control $status %>
<tr class="gap__$Top.ID"><td style="width:20%">$ID - $Created.nice</td><td class="Fail_$failed" colspan=5>
$Title
</td></tr>
<% end_control %>
</tbody>
