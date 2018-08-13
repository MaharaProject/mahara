    <entry>
        <title>{$title}</title>
        <id>{$id}</id>
{if $author}        <author>
            <name>{$author}</name>
        </author>
{/if}
{if $updated}        <updated>{$updated}</updated>{/if}
{if $created}        <published>{$created}</published>{/if}
{if $summary}        <summary{if $summarytype != 'text'} type="{$summarytype}"{/if}>{if $summarytype == 'xhtml'}<div xmlns="http://www.w3.org/1999/xhtml">{/if}{if $summarytype == 'xhtml'}{$summary|export_leap_rewrite_links|safe}{elseif $summarytype == 'html'}{$summary|export_leap_rewrite_links}{else}{$summary}{/if}{if $summarytype == 'xhtml'}</div>{/if}</summary>{/if}
{if $instructions}   <subtitle{if $instructionstype != 'text'} type="{$instructionstype}"{/if}>{if $instructionstype == 'xhtml'}<div xmlns="http://www.w3.org/1999/xhtml">{/if}{if $instructionstype == 'xhtml'}{$instructions|export_leap_rewrite_links|safe}{elseif $instructionstype == 'html'}{$instructions|export_leap_rewrite_links}{else}{$instructions}{/if}{if $instructionstype == 'xhtml'}</div>{/if}</subtitle>{/if}
        <content{if $contenttype != 'text' && $contenttype != ''} type="{$contenttype}"{/if}>{if $contenttype == 'xhtml'}<div xmlns="http://www.w3.org/1999/xhtml">{/if}{if $contenttype == 'xhtml'}{$content|export_leap_rewrite_links|safe}{elseif $contenttype == 'html'}{$content|export_leap_rewrite_links}{else}{$content}{/if}{if $contenttype == 'xhtml'}</div>{/if}</content>
        <rdf:type rdf:resource="leap2:{$leaptype}"/>
{if $artefacttype}        <mahara:artefactplugin mahara:type="{$artefacttype}" mahara:plugin="{$artefactplugin}"/>{/if}
{include file="export:leap:links.tpl"}
{include file="export:leap:categories.tpl"}
{foreach from=$dates item=date}
        <leap2:date leap2:point="{$date.point}"{if $date.label} leap2:label="{$date.label}"{/if}>{$date.date}</leap2:date>
{/foreach}
{if !$skipfooter}
{include file="export:leap:entryfooter.tpl"}
{/if}
