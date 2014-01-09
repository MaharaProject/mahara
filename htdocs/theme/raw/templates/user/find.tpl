{include file="header.tpl"}
            {$form|safe}
            <h2 id="searchresultsheading" class="accessible-hidden hidden">{str tag=Results}</h2>
            <div id="friendslist" class="fullwidth listing">
{$results.tablerows|safe}
            </div>
{$results.pagination|safe}
{include file="footer.tpl"}
