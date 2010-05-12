{auto_escape off}
{include file="export:leap:entry.tpl" skipfooter=true}
{foreach from=$persondata item=entry}
        <leap:persondata
            mahara:artefactplugin="{$entry->artefactplugin|escape}"
            mahara:artefacttype="{$entry->artefacttype|escape}"
{if $entry->mahara}
            leap:field="other" mahara:field="{$entry->field|escape}"
{else}
            leap:field="{$entry->field|escape}"
{/if}
            leap:label="{$entry->label|escape}"
{if $entry->service}
            leap:service="{$entry->service|escape}"
{/if}
        >{$entry->value|escape}</leap:persondata>
{/foreach}
{if $spacialdata}
        <leap:spatial>
{foreach from=$spacialdata item=entry}
            <leap:{$entry->type|escape} mahara:artefacttype="{$entry->artefacttype|escape}"{if $entry->countrycode} leap:countrycode="{$entry->countrycode|escape}"{/if}>{$entry->value|escape}</leap:{$entry->type|escape}>
{/foreach}
        </leap:spatial>
{/if}
{include file="export:leap:entryfooter.tpl"}
{/auto_escape}
