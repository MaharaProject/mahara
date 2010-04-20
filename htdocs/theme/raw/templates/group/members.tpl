{include file="header.tpl"}

    {$form}
    {if $membershiptype}<h3>{str tag=pendingmembers section=group}</h3>{/if}
    <div id="results">
        <table id="membersearchresults" class="tablerenderer fullwidth listing twocolumn">
            <tbody>
            {$results}
            </tbody>
        </table>
    </div>
    {$pagination}

{include file="footer.tpl"}
