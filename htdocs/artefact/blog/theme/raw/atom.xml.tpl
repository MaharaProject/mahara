<?xml version="1.0"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{$feed.title}</title>
    <id>{$feed.id}</id>
    <link href="{$feed.link}" />
    <link rel="self" type="application/atom+xml" href="{$feed.selflink}" />
    <subtitle type="html"><![CDATA[ {$feed.description|clean_html|safe} ]]></subtitle>
    <logo>{$feed.logo}</logo>
    <icon>{$feed.icon}</icon>
    <generator uri="{$feed.generator.uri}" version="{$feed.generator.version}">
        {$feed.generator.text}
    </generator>
    <author>
        <name>{$feed.author.name}</name>
    {if $feed.author.uri}
        <uri>{$feed.author.uri}</uri>
    {/if}
    </author>
    <updated>{$feed.updated}</updated>
    <rights type="html"><![CDATA[ {$feed.rights} ]]></rights>
{foreach from=$posts item=post}
    <entry>
        <title>{$post.title}</title>
        <id>{$post.id}</id>
        <link href="{$post.link}" />
        <content type="html"><![CDATA[ {$post.description|clean_html|safe} ]]></content>
        <author>
            <name>{$feed.author.name}</name>
        </author>
        <updated>{$post.mtime}</updated>
        <rights type="html"><![CDATA[ {$feed.rights} ]]></rights>
    {foreach from=$post.attachments item=attachlink}
        <link rel="enclosure" title="{$attachlink.title}" href="{$attachlink.link}" />
    {/foreach}
    </entry>
{/foreach}
</feed>
