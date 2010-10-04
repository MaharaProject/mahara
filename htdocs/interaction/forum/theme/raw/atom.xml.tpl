<?xml version="1.0"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{$feed.title}</title>
    <id>{$feed.id}</id>
    <link href="{$feed.link}" />
    <link rel="self" type="application/atom+xml" href="{$feed.selflink}" />
    <logo>{$feed.logo}</logo>
    <icon>{$feed.icon}</icon>
    <generator uri="{$feed.generator.uri}" version="{$feed.generator.version}">
        {$feed.generator.text}
    </generator>
    <updated>{$feed.updated}</updated>
{foreach from=$posts item=post}
    <entry>
        <title>{$post.title}</title>
        <id>{$post.id}</id>
        <link href="{$post.link}" />
        <content type="html"><![CDATA[ {$post.description|clean_html|safe} ]]></content>
        <author>
            <name>{$post.author}</name>
        </author>
        <updated>{$post.mtime}</updated>
    </entry>
{/foreach}
</feed>

