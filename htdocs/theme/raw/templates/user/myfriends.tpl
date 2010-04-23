{include file="header.tpl"}
<div id="friendslistcontainer">
    {$form}
{if $results}
    <table id="friendslist" class="fullwidth listing">
        <tbody>
        {$results.tablerows}
        </tbody>
    </table>
{$results.pagination}
{/if}
{if $message}
    <div class="message">{$message}</div>
{/if}
</div>
{include file="footer.tpl"}
