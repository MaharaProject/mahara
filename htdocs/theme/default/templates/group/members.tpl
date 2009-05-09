{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<form action="{$WWWROOT}group/members.php" method="post">
    <input type="hidden" id="groupid" name="id" value="{$GROUP->id|escape}">
    <div class="searchform">
        <label>{str tag='Query' section='admin'}:
            <input type="text" name="query" id="query" value="{$query|escape}">
        </label>
        <button id="query-button" type="submit">{str tag="go"}</button>
        {if $membershiptype}<input type="hidden" name="membershiptype" value="{$membershiptype|escape}" />{/if}
    </div>
</form>
    {if $membershiptype}<h3>{str tag=pendingmembers section=group}</h3>{/if}
    <div id="results">
        <table id="membersearchresults" class="tablerenderer fullwidth">
            <tbody>
            {$results}
            </tbody>
        </table>
    </div>
    {$pagination}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
