{foreach from=$items item=item}
        <div class="{cycle values='r0,r1'} listrow">
            <h3 class="title"><a href="{group_homepage_url($item)}">{$item->name}</a><span class="roledisplay"> - {$item->roledisplay}</span></h3>
            {if $item->description}
                <div class="details">{$item->description|str_shorten_html:100:true|safe}</div>
            {/if}
        </div>
{/foreach}