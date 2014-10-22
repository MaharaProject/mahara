{include file="header.tpl"}
{if $message}
    <div class="message">
        <h3>{$subheading}</h3>
        <p>{$message}</p>
        {$form|safe}
    </div>
{else}
    <h3>{$subheading}</h3>
    {$form|safe}
{/if}
{include file="footer.tpl"}