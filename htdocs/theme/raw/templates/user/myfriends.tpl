{include file="header.tpl"}
    {$form|safe}
{if $message}
    <div class="message">{$message|safe}</div>
{/if}
{if $results}
    <div id="friendslist" class="fullwidth listing">
        {$results.tablerows|safe}
    </div>
{$results.pagination|safe}
{/if}
{include file="footer.tpl"}
