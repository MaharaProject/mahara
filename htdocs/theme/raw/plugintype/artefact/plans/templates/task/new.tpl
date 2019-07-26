{include file="header.tpl"}
<button id="view_button" class="btn btn-secondary icon icon-regular icon-eye" type="button" title="{$showassignedview}">
</button>
<button id="outcome_button" class="btn btn-secondary icon icon-regular icon-eye" type="button" title="{$showassignedoutcome}">
</button>
{$form|safe}
{include file="pagemodal.tpl"}
{include file="footer.tpl"}
