{auto_escape off}
<div>
    <h3>
        <a href="{$WWWROOT}group/view.php?id={$group->id}">
            {$group->name|escape}
        </a>
    </h3>
    {if $group->description}
    <p>
        {$group->description}
    </p>
    {/if}
</div>
{/auto_escape}
