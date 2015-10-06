{include file="header.tpl"}
{$form|safe}
<script language="javascript" type="application/javascript" src="{$WWWROOT}search/elasticsearch/filter.js"></script>
<div id="universalsearchresult">
    {$results.tablerows|safe}
</div>
{$results.pagination|safe}

{include file="footer.tpl"}