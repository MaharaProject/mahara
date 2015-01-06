{foreach from=$categories key=name item=category}
    <div id="block-category-{$category.name}" class="block-category-title collapsed">
        <div class="withjs" style="display: none" title="{$category.description}">{$category.title}</div>
        <a class="nonjs" href="{$WWWROOT}view/blocks.php?id={$viewid}&c={$category.name}&new=1" title="{$category.description}">{$category.title}</a>
    </div>
    {if $selectedcategory == $category.name}
        <div id="{$category.name}">
            {$blocktypelist|safe}
        </div>
    {else}
        <div id="{$category.name}" class="hidden">{str tag=loading section=mahara}</div>
    {/if}
{/foreach}
