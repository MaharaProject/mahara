{auto_escape off}
<ul>
{foreach from=$categories key=name item=category}
    <li class="{$category.class}"><a href="{$WWWROOT}view/blocks.php?id={$viewid}&amp;c={$category.name|escape}&amp;new={$new}">{$category.title|escape}</a></li>
{/foreach}
</ul>
{/auto_escape}
