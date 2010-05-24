{include file="header.tpl"}
<div id="friendslistcontainer">
    {$form|safe}
{if $results}
    <table id="friendslist" class="fullwidth listing">
        <tbody>
        {$results.tablerows|safe}
        </tbody>
    </table>
{$results.pagination|safe}
{/if}
{if $message}
    <div class="message">{$message|safe}</div>
{/if}
</div>
{include file="footer.tpl"}
