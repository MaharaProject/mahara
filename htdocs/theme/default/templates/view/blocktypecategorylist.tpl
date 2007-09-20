<ul>
{foreach from=$categories item=category}
    <li class="{$category.class}"><a href="{$WWWROOT}view/blocks.php?id={$viewid}&amp;c={$category.name | escape}">{$category.title | escape}</a></li>
{/foreach}
</ul>
