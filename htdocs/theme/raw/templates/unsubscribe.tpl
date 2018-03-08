{include file="header.tpl"}
<p>{$heading}</p>
{if $unsubscribed}
    <div class="alert alert-success">{str tag="unsubscribesuccess" section="notification.email"}</div>
{else}
    <div class="alert alert-danger">{str tag="unsubscribefailed1" section="notification.email"}</div>
{/if}
{include file="footer.tpl"}
