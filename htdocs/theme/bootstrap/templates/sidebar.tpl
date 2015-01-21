{foreach from=$blocks item=sideblock}{strip}
    {counter name="sidebar" assign=sequence}
    {/strip}<div{if $sideblock.id} id="{$sideblock.id}"{/if} class="sideblock sideblock-{$sequence} panel panel-default">
{include file="sideblocks/`$sideblock.name`.tpl" sbdata=$sideblock.data}

</div>
{/foreach}

