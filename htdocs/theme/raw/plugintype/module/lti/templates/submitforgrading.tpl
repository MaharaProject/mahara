{include file="header.tpl"}
{if $error}
    {$error}
{else}
    <p>{str tag="submitintro" section="module.lti" arg1=$WWWROOT}</p>

    {$form|safe}
{/if}
{include file="footer.tpl"}