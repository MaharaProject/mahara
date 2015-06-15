{include file="header.tpl"}
    {$editform|safe}
    {if $enablemultipleblogstext}
    <p class="s dull text-center text-muted">
        {str tag=enablemultipleblogstext section=artefact.blog arg1=$WWWROOT}
    </p>
    {/if}
{include file="footer.tpl"}
