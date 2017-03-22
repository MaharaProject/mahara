{include file="header.tpl"}

{if $GROUP->description}
    <div class="groupdescription view-description lead">
        {$GROUP->description|clean_html|safe}
    </div>
{/if}
<div class="grouphomepage">
    {$viewcontent|safe}
</div>

{include file="footer.tpl"}
