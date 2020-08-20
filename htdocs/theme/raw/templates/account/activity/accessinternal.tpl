<p>You have been given access to the following:</p>
{foreach from=$accessitems item=item}
{if count($accessitems) == 1}
{$item.name|clean_html|safe}
{else}
<ul class="list-unstyled">
<li style='padding: 0.5em'>{$item.name|clean_html|safe} [<a href={$item.url}>{$item.url}</a>]</li>
</ul>
{/if}
{/foreach}
{strip}
{if accessdatemsg}
<p>{$accessdatemsg}</p>
{/if}
{/strip}
