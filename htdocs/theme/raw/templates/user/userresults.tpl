{if $data}
{foreach from=$data item=user}
    {if $page == 'myfriends'}
    {include file="user/user.tpl" user=$user page=$page admingroups=$admingroups}
    {elseif $page == 'find'}
    {include file="user/userfind.tpl" user=$user page=$page admingroups=$admingroups}
    {/if}
{/foreach}
{elseif $query}
    <div class="no-result panel-body">{str tag=nosearchresultsfound section=group}</div>
{/if}
