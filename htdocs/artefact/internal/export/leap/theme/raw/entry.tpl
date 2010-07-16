{include file="export:leap:entry.tpl" skipfooter=true}
{foreach from=$persondata item=entry}
        <leap2:persondata
            mahara:artefactplugin="{$entry->artefactplugin}"
            mahara:artefacttype="{$entry->artefacttype}"
{if $entry->mahara}
            leap2:field="other" mahara:field="{$entry->field}"
{else}
            leap2:field="{$entry->field}"
{/if}
            leap2:label="{$entry->label}"
{if $entry->service}
            leap2:service="{$entry->service}"
{/if}
        >{$entry->value}</leap2:persondata>
{/foreach}
{if $spacialdata}
        <leap2:spatial>
{foreach from=$spacialdata item=entry}
            <leap2:{$entry->type} mahara:artefacttype="{$entry->artefacttype}"{if $entry->countrycode} leap2:countrycode="{$entry->countrycode}"{/if}>{$entry->value}</leap2:{$entry->type}>
{/foreach}
        </leap2:spatial>
{/if}
{include file="export:leap:entryfooter.tpl"}
