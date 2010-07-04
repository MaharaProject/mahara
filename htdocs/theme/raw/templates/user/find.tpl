{include file="header.tpl"}
<div id="friendslistcontainer">
            {$form|safe}
            <table id="friendslist" class="fullwidth listing">
                <tbody>
{$results.tablerows|safe}
                </tbody>
            </table>
            </div>
{$results.pagination|safe}
</div>
{include file="footer.tpl"}
