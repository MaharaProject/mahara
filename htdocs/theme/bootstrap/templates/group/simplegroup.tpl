<div>
    <h3>
        <a href="{group_homepage_url($group)}">
            {$group->name}
        </a>
    </h3>
    {if $group->description}
    <p>
        {$group->description|clean_html|safe}
    </p>
    {/if}
</div>
