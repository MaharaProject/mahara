{foreach from=$categories key=name item=category name=default}
    <div id="block-category-{$category.name}" class="block-category-title hide-title-collapsed btn btn-primary text-left" title="{$category.description}">
        <span class="icon icon-sm icon-chevron-down indicator" role="presentation" aria-hidden="true"></span>
        <span class="icon icon-{$category.name} block-icon" role="presentation" aria-hidden="true"></span>

        <span class="title">{$category.title}</span>
    </div>
    {if $selectedcategory == $category.name}
        <div id="{$category.name}">
            {$blocktypelist|safe}
        </div>
    {else}
        <div id="{$category.name}">
            <div class="metadata hide-title-collapsed">
                <span class="icon icon-spin icon-spinner icon left" role="presentation" aria-hidden="true"></span>
                <span class="title">{str tag=loading section=mahara}</span>
            </div>
        </div>
    {/if}
{/foreach}

<div class="btn btn-primary text-right last collapse-expand" aria-hidden="true" data-trigger="col-collapse">
    <span class="icon icon-angle-double-left" role="presentation" aria-hidden="true"></span>
    <span class="icon icon-angle-double-right" role="presentation" aria-hidden="true"></span>
    <span class="sr-only">{str tag=expandcontract section=view}</span>
</div>
