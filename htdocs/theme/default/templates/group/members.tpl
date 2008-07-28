{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{$group->name|escape}</h2>

{include file="group/tabstart.tpl" current="members"}

<form action="{$WWWROOT}group/members.php" method="post">
    <input type="hidden" id="groupid" name="id" value="{$group->id|escape}">
    <div class="searchform center" style="margin-bottom: .5em;">
        <label>{str tag='Query' section='admin'}:
            <input type="text" name="query" id="query" value="{$query|escape}">
        </label>
        <button id="query-button" type="submit">{str tag="go"}</button>
        {if $membershiptype}<input type="hidden" name="membershiptype" value="{$membershiptype|escape}" />{/if}
    </div>
</form>
    {if $membershiptype}<h3>{str tag=pendingmembers section=group}</h3>{/if}
    <div id="results">
        <table id="membersearchresults" class="tablerenderer">
            <tbody>
            {$results}
            </tbody>
        </table>
    </div>
    <div id="pagination">{$pagination}</div>
    <script type="text/javascript">{$pagination_js}</script>


{include file="group/tabend.tpl"}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
