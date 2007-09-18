<ul>
{foreach from=$categories item=category}
    <li class="{$category.class}"><a href="{$WWWROOT}viewrework.php?view={$viewid}&amp;category={$category.name | escape}">{$category.title | escape}</a></li>
{/foreach}
</ul>
