{include file="header.tpl"}
{if $accesslistmaximum}
<div class="alert alert-info">
    {str tag=shareallwithmaximum section=view arg1=$accesslistmaximum arg2=$accesslistmaximum}
</div>
{/if}
{$form|safe}
{include file="progress_meter.tpl"}
{include file="footer.tpl"}
