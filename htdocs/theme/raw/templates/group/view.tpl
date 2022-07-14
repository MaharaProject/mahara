{include file="header.tpl"}
<div class="row grouphomepage-desc-status view-container">
    <div class="col-md-8">
    {if $GROUP->description}
        <div class="groupdescription view-description lead">
            {$GROUP->description|clean_html|safe}
        </div>
    {/if}
    </div>
    <div class="group-user-status col-md-4">
        {include file="group/groupuserstatus.tpl" group=$group}
    </div>
</div>
<div class="grouphomepage">
    <div class="grid-stack">
    {if $viewcontent}
        {$viewcontent|safe}
    {/if}
    </div>
</div>
<div class="metadata text-end last-updated">
    {$lastupdatedstr}{if $visitstring}; {$visitstring}{/if}
</div>
{include file="footer.tpl"}
