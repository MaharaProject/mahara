{include file="export:leap:entry.tpl" skipfooter=true}
        <mahara:view{if $layout} mahara:layout="{$layout|escape}"{/if}{if $type} mahara:type="{$type|escape}"{/if} mahara:ownerformat="{$ownerformat|escape}">
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
{include file="export:leap:entryfooter.tpl"}
