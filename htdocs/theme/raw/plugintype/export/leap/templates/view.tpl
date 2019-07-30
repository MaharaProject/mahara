{include file="export:leap:entry.tpl" skipfooter=true}
        <mahara:view{if $newlayout} mahara:newlayout="1"{/if}{if $layout} mahara:layout="{$layout}"{/if}{if $type} mahara:type="{$type}"{/if} mahara:ownerformat="{$ownerformat}">

{if !$newlayout}
    {foreach from=$viewdata item=row}
        <mahara:row>
    {foreach from=$row['columns'] item=column}
            <mahara:column>
    {foreach from=$column item=blockinstance}
                <mahara:blockinstance mahara:blocktype="{$blockinstance.blocktype}" mahara:blocktitle="{$blockinstance.title}">
    {foreach from=$blockinstance.config key=fieldname item=fieldvalue}
                    <mahara:{$fieldname}>{$fieldvalue}</mahara:{$fieldname}>
    {/foreach}
                </mahara:blockinstance>
    {/foreach}
            </mahara:column>
    {/foreach}
        </mahara:row>
    {/foreach}
{else}
    {foreach from=$blocks item=bi}
        <mahara:blockinstance mahara:blocktype="{$bi.blocktype}" mahara:blocktitle="{$bi.title}" mahara:positionx="{$bi.positionx}" mahara:positiony="{$bi.positiony}" mahara:height="{$bi.height}" mahara:width="{$bi.width}">
    {foreach from=$bi.config key=fieldname item=fieldvalue}
            <mahara:{$fieldname}>{$fieldvalue}</mahara:{$fieldname}>
    {/foreach}
        </mahara:blockinstance>
    {/foreach}
{/if}
        </mahara:view>
{include file="export:leap:entryfooter.tpl"}
