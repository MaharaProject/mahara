
<p>{str tag=emailheader section=notification.email arg1=$sitename}</p>
------------------------------------------------------------------------
<p>You have been given access to the following:</p>
{foreach from=$accessitems item=item}
{if count($accessitems) == 1}
<a href={$item.url}>{$item.name|clean_html|safe}</a>
{else}
<ul class="list-unstyled">
    <li style='padding: 0.5em'><a href={$item.url}>{$item.name|clean_html|safe}</a></li>
</ul>
{/if}
{/foreach}
{strip}
{if accessdatemsg}
    <p>{$accessdatemsg}</p>
{/if}
{/strip}
<p>{str (tag=referurl section=notification.email arg1=$url)|clean_html|safe}</p>
------------------------------------------------------------------------
<p>{str tag=emailfooter section=notification.email arg1=$sitename, arg2=$prefurl|clean_html|safe}</p>
