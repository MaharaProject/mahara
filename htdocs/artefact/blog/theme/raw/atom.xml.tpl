<?xml version="1.0"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{$feed.title}</title>
    <id>{$feed.id}</id>
    <link href="{$feed.link|escape}" />
    <link rel="self" type="application/atom+xml" href="{$feed.selflink|escape}" />
    <subtitle type="html"><![CDATA[ {$feed.description} ]]></subtitle>
    <logo>{$feed.logo|escape}</logo>
    <icon>{$feed.icon|escape}</icon>
    <generator uri="{$feed.generator.uri|escape}" version="{$feed.generator.version}">
        {$feed.generator.text}
    </generator>
    <author>
        <name>{$feed.author.name}</name>
    {if $feed.author.uri}
        <uri>{$feed.author.uri|escape}</uri>
    {/if}
    </author>
    <updated>{$feed.updated}</updated>
    <rights type="html"><![CDATA[ {$feed.rights} ]]></rights>
{foreach from=$posts item=post}
    <entry>
        <title>{$post.title}</title>
        <id>{$post.id}</id>
        <link href="{$post.link|escape}" />
        <content type="html"><![CDATA[ {$post.description} ]]></content>
        <author>
            <name>{$feed.author.name}</name>
        </author>
        <updated>{$post.mtime}</updated>
        <rights type="html"><![CDATA[ {$feed.rights} ]]></rights>
    {foreach from=$post.attachments item=attachlink}
        <link rel="enclosure" title="{$attachlink.title}" href="{$attachlink.link|escape}" />
    {/foreach}
    </entry>
{/foreach}
</feed>
