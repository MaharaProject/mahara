{include file="header.tpl"}
<script language="javascript" type="text/javascript" src="{$WWWROOT}search/elasticsearch/filter.js"></script>
<div id="universal-search-container">
    {$form|safe}
</div>
<div id="universalsearchresult">
    {$results.tablerows|safe}
</div>
{$results.pagination|safe}

{include file="footer.tpl"}