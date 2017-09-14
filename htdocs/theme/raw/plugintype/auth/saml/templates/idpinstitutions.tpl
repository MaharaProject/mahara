{if $r.institutions}
    {foreach from=$r.institutions item=institution name=ags}
        {if !$institution->site}<a href="{$WWWROOT}institution/index.php?institution={$institution->name}">{/if}
        {$institution->displayname}
        {if !$institution->site}</a>{/if}
        {if !$dwoo.foreach.ags.last}, {/if}
    {/foreach}
{/if}