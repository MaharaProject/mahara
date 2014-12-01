{foreach from=$categories key=name item=category}
    <div id="block-category-{$category.name}" class="block-category-title collapsed">
        <div class="withjs" title="{$category.description}">{$category.title}</div>
    </div>
    {if $selectedcategory == $category.name}
        <div id="{$category.name}">
            {$blocktypelist|safe}
        </div>
    {else}
        <div id="{$category.name}" class="hidden">{str tag=loading section=mahara}</div>
    {/if}
{/foreach}
