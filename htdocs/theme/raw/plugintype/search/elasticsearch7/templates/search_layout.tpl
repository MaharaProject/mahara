{include file="header.tpl"}
<a name="onsearch"></a>
{$form|safe}
<div id="universalsearchresult">
    {$results.tablerows|safe}
</div>
{$results.pagination|safe}

{include file="footer.tpl"}