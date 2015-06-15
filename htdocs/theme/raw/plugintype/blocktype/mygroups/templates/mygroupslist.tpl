
<div class="list-group">
    {foreach from=$items item=item}
    <div class="list-group-item list-group-item-link">
        <a href="{group_homepage_url($item)}">{$item->name} <span class="metadata"> - {$item->roledisplay}</span></a>
        {if $item->description}
            <div class="details text-small mtm">
            {$item->description|str_shorten_html:100:true|safe}
            </div>
        {/if}
    </div>
    {/foreach}
</div>