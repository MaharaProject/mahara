{include file="header.tpl"}
<div id="friendslistcontainer">
    {$form|safe}
{if $message}
    <div class="message">{$message|safe}</div>
{/if}
{if $results}
    <table id="friendslist" class="fullwidth listing">
        <tbody>
        {$results.tablerows|safe}
        </tbody>
    </table>
{$results.pagination|safe}
{/if}
</div>
{include file="footer.tpl"}
