{foreach from=$path item=f name=path}
    {if !$.foreach.path.first}/ 

    {/if}
    
    {if $.foreach.path.last}
        {$f->title|str_shorten_text:34}
    {else}
        <a href="{$querybase|safe}folder={$f->id}{if $owner}&owner={$owner}{if $ownerid}&ownerid={$ownerid}{/if}{/if}" class="secondary-link changefolder{if $f->class} {$f->class}{/if}">
            {$f->title|str_shorten_text:34}
        </a>
    {/if}
{/foreach}

