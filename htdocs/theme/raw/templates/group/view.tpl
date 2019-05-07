{include file="header.tpl"}

{if $GROUP->description}
    <div class="groupdescription view-description lead">
        {$GROUP->description|clean_html|safe}
    </div>
{/if}
<div class="grouphomepage">
    <div class="grid-stack">
    {if $viewcontent}
        {$viewcontent|safe}
    {/if}
    </div>
</div>
<div class="metadata text-right">
    {$lastupdatedstr}{if $visitstring}; {$visitstring}{/if}
</div>
{include file="footer.tpl"}
