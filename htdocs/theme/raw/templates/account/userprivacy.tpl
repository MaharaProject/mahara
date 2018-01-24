{include file="header.tpl"}

{if $loginanyway}
    <p class="lead alert alert-warning">
        {$loginanyway|safe}
    </p>
{/if}
<div class="lead">{$description}</div>
<div>{$form|safe}</div>

{include file="privacy_modal.tpl"}

{include file="footer.tpl"}
