{include file="export:leap:entry.tpl" skipfooter=true}
{foreach from=$persondata item=entry}
        <leap:persondata
            mahara:artefactplugin="{$entry->artefactplugin}"
            mahara:artefacttype="{$entry->artefacttype}"
{if $entry->mahara}
            leap:field="other" mahara:field="{$entry->field}"
{else}
            leap:field="{$entry->field}"
{/if}
            leap:label="{$entry->label}"
{if $entry->service}
            leap:service="{$entry->service}"
{/if}
        >{$entry->value}</leap:persondata>
{/foreach}
{if $spacialdata}
        <leap:spatial>
{foreach from=$spacialdata item=entry}
            <leap:{$entry->type} mahara:artefacttype="{$entry->artefacttype}"{if $entry->countrycode} leap:countrycode="{$entry->countrycode}"{/if}>{$entry->value}</leap:{$entry->type}>
{/foreach}
        </leap:spatial>
{/if}
{include file="export:leap:entryfooter.tpl"}
