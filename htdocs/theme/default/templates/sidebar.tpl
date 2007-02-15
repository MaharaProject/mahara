<div id="column-right">
{foreach from=$SIDEBLOCKS item=sideblock}
    {counter name="sidebar" assign=SIDEBAR_SEQUENCE}
    {if $SIDEBAR_SEQUENCE > 3}{assign var=SIDEBAR_SEQUENCE value=3}{/if}
    {if $sideblock.id}
    <div id="{$sideblock.id|escape}" class="sidebar sidebar_{$SIDEBAR_SEQUENCE}">
    {else}
    <div class="sidebar sidebar_{$SIDEBAR_SEQUENCE}">
    {/if}

    {assign var="sideblock_name" value=$sideblock.name}
    {include file="sideblocks/$sideblock_name.tpl" data=$sideblock.data}

        <div class="sidebar-botcorners"><img src="{theme_path location='images/sidebox_bot.gif'}" border="0" alt=""></div>
    </div>
{/foreach}
</div>
