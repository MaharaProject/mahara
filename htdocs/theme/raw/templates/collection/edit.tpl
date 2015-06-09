{include file="header.tpl"}
{if $GROUP}
    <h2>{$PAGESUBHEADING}{if $SUBPAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}</h2>
{/if}
{$form|safe}
{include file="footer.tpl"}
