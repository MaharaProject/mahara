
<p>{str tag=emailheader section=notification.email arg1=$sitename}</p>
------------------------------------------------------------------------
<p>You have been given access to the following:</p>
<ul class="list-unstyled">
    {foreach from=$accessitems item=item}
        <li style='padding: 0.5em'>{$item.name|clean_html|safe}   <a href={$item.url}>{$item.url}</a></li>
    {/foreach}
</ul>
{strip}
{if accessdatemsg}
    <p>{$accessdatemsg}</p>
{/if}
{/strip}
<p>{str (tag=referurl section=notification.email arg1=$url)|clean_html|safe}</p>
------------------------------------------------------------------------
<p>{str tag=emailfooter section=notification.email arg1=$sitename, arg2=$prefurl|clean_html|safe}</p>
