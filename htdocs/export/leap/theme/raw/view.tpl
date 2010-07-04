{include file="export:leap:entry.tpl" skipfooter=true}
        <mahara:view{if $layout} mahara:layout="{$layout}"{/if}{if $type} mahara:type="{$type}"{/if} mahara:ownerformat="{$ownerformat}">
{foreach from=$viewdata item=column}
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
        </mahara:view>
{include file="export:leap:entryfooter.tpl"}
