{foreach from=$blocks item=sideblock}{strip}
    {counter name="sidebar" assign=sequence}
    {assign var="sideblock_name" value=$sideblock.name}
    {/strip}<div{if $sideblock.id} id="{$sideblock.id|escape}"{/if} class="sideblock sideblock-{$sequence}">
{include file="sideblocks/$sideblock_name.tpl" data=$sideblock.data}

</div>
{/foreach}
