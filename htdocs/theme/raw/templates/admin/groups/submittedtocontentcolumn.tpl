{if $r.group}
    {if !$r.groupdeleted}<a href="{$WWWROOT}group/view.php?id={$r.group}">{/if}
        {$r.submittedto}
    {if !$r.groupdeleted}</a>{/if}
{elseif $r.externalhost}
    <a href="{$r.externalhost}">
    {if $r.externalname}
        {$r.externalname}
    {else}
        {$r.externalhost}
    {/if}
    </a>
{/if}