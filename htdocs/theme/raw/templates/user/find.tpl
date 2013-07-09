{include file="header.tpl"}
            {$form|safe}
            <div id="friendslist" class="fullwidth listing">
{$results.tablerows|safe}
            </div>
{$results.pagination|safe}
{include file="footer.tpl"}
