<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
>
    <id>{$id}</id>
    <title>{$title}</title>
    <updated>{$updated}</updated>
    <generator uri="https://mahara.org/" version="{$version}">Mahara</generator>
    <author>
        <name>{$USER->firstname} {$USER->lastname}</name>
        <email>{$USER->email}</email>
        <uri>webservice:{$functionname}</uri>
    </author>
{foreach from=$entries item=entry key=idx}{if $entry['id'] and $entry['title']}
    <entry>
        <id>{$entry['id']}</id>
        <title>{$entry['title']}</title>
        <link href="{$entry['id']}"/>
        {if $entry['name'] or $entry['email']}<author>
            {if $entry['name']}<name>{$entry['name']}</name>{/if}
            {if $entry['email']}<email>{$entry['email']}</email>{/if}
        </author> {/if}
        {if $entry['updated']}<updated>{$entry['updated']}</updated>{/if}
        {if $entry['published']}<published>{$entry['published']}</published>{/if}
        {if $entry['summary']}<summary>{$entry['summary']}</summary>{/if}
        {if $entry['content']}<content type="xhtml">{$enty['content']}</content>{/if}
    </entry>{/if}{/foreach}
</feed>
