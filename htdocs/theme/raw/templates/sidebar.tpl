{foreach from=$blocks item=sideblock}{strip}
    {counter name="sidebar" assign=sequence}
    {/strip}<div{if $sideblock.id} id="{$sideblock.id}"{/if} class="sideblock-{$sequence} {if $sideblock.class}{$sideblock.class}{/if}">
    {if $sideblock.template}
    {include file=$sideblock.template sbdata=$sideblock.data}
    {else}
    {include file="sideblocks/generic.tpl" sbdata=$sideblock}
    {/if}
</div>
{/foreach}

