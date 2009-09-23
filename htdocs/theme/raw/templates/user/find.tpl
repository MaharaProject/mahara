{include file="header.tpl"}
            <div id="friendslistcontainer">
            {$form}
            <table id="friendslist" class="fullwidth listing">
                <tbody>
{$results.tablerows}
                </tbody>
            </table>
            </div>
{$results.pagination}
</div>
{include file="footer.tpl"}
