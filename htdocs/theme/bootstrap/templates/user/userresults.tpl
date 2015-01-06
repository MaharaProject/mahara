{if $data}
{foreach from=$data item=user}
    <div class="{cycle values='r0,r1'}">
        {include file="user/user.tpl" user=$user page=$page admingroups=$admingroups}
    </div>
{/foreach}
{elseif $query}
    <div class="message">{str tag=nosearchresultsfound section=group}</div>
{/if}
