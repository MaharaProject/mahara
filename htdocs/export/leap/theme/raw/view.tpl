{include file="export:leap:entry.tpl" skipfooter=true}
{if $ownerformat}        <mahara:ownerformat>{$ownerformat|escape}</mahara:ownerformat>
{/if}
        <mahara:view>
{foreach from=$viewdata item=column}
            <mahara:column>
{foreach from=$column item=blockinstance}
                <mahara:blockinstance mahara:blocktype="{$blockinstance.blocktype|escape}" mahara:blocktitle="{$blockinstance.title|escape}">
{foreach from=$blockinstance.config key=fieldname item=fieldvalue}
                    <mahara:{$fieldname|escape}>{$fieldvalue|escape}</mahara:{$fieldname|escape}>
{/foreach}
                </mahara:blockinstance>
{/foreach}
            </mahara:column>
{/foreach}
        </mahara:view>
        <mahara:viewlayout>TODO</mahara:viewlayout>
{include file="export:leap:entryfooter.tpl"}
