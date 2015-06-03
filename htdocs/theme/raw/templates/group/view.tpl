{include file="header.tpl"}

{if $GROUP->description}
    <div class="groupdescription lead">
        {$GROUP->description|clean_html|safe}
    </div>
{/if}
<div class="grouphomepage ptl">
    {$viewcontent|safe}
</div>

{include file="footer.tpl"}
