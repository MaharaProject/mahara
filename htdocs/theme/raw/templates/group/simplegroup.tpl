<div>
    <h3>
        <a href="{$WWWROOT}group/view.php?id={$group->id}">
            {$group->name}
        </a>
    </h3>
    {if $group->description}
    <p>
        {$group->description|clean_html|safe}
    </p>
    {/if}
</div>
