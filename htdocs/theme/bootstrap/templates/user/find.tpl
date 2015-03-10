{include file="header.tpl"}
<div class="ptl pbl">
    {$form|safe}
</div>
<div class="panel panel-default mtl">
    <h2 id="searchresultsheading" class="panel-heading">{str tag=Results}</h2>
    <div class="panel-body">
        <div id="friendslist" class="fullwidth listing">
            {$results.tablerows|safe}
        </div>
    </div>
</div>
{$results.pagination|safe}
{include file="footer.tpl"}
