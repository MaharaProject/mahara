{if $data}
{foreach from=$data item=user}
                    <tr class="r{cycle values=1,0}">
{include file="user/user.tpl" user=$user page=$page}
                    </tr>
{/foreach}
{elseif $query}
  <tr><td><div class="message">{str tag=nosearchresultsfound section=group}</div></td></tr>
{/if}