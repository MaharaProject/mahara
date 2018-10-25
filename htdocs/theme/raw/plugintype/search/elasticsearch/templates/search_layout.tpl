{include file="header.tpl"}
<a name="onsearch"></a>
{$form|safe}
<script src="{$WWWROOT}search/elasticsearch/filter.js"></script>
<div id="universalsearchresult">
    {$results.tablerows|safe}
</div>
{$results.pagination|safe}

{include file="footer.tpl"}