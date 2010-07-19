{auto_escape on}
{include file="header.tpl"}
    <div class="rbuttons">
        <a class="btn btn-add" href="{$WWWROOT}collection/create.php">{str section="collection" tag="newcollection"}</a>
    </div>
{if !$collections}
        <div class="message">{$strnocollectionsaddone|safe}</div>
{else}
    <table id="mycollections" class="fullwidth listing">
        <tbody>
            {$collections.tablerows|safe}
        </tbody>
    </table>
       {$collections.pagination|safe}
{/if}
{include file="footer.tpl"}
{auto_escape off}
