{include file="header.tpl"}

    {$form|safe}
    <p>{$instructions}</p>
    {if $membershiptype}<h3>{str tag=pendingmembers section=group}</h3>{/if}
    <div id="results">
        <table id="membersearchresults" class="tablerenderer fullwidth listing twocolumn">
            <tbody>
            {$results|safe}
            </tbody>
        </table>
    </div>
    {$pagination|safe}

{include file="footer.tpl"}
