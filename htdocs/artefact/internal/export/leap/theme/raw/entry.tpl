{include file="export:leap:entry.tpl" skipfooter=true}
{foreach from=$persondata item=entry}
        <leap2:persondata
            mahara:artefactplugin="{$entry->artefactplugin|escape}"
            mahara:artefacttype="{$entry->artefacttype|escape}"
{if $entry->mahara}
            leap2:field="other" mahara:field="{$entry->field|escape}"
{else}
            leap2:field="{$entry->field|escape}"
{/if}
            leap2:label="{$entry->label|escape}"
{if $entry->service}
            leap2:service="{$entry->service|escape}"
{/if}
        >{$entry->value}</leap2:persondata>
{/foreach}
{if $spacialdata}
        <leap2:spatial>
{foreach from=$spacialdata item=entry}
            <leap2:{$entry->type|escape} mahara:artefacttype="{$entry->artefacttype|escape}"{if $entry->countrycode} leap2:countrycode="{$entry->countrycode|escape}"{/if}>{$entry->value}</leap2:{$entry->type}>
{/foreach}
        </leap2:spatial>
{/if}
{include file="export:leap:entryfooter.tpl"}
