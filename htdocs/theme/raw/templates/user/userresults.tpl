{if $data}
{foreach from=$data item=user}
    {if $page == 'myfriends'}
    {include file="user/user.tpl" user=$user page=$page admingroups=$admingroups mrmoduleactive=$mrmoduleactive}
    {elseif $page == 'find'}
    {include file="user/userfind.tpl" user=$user page=$page admingroups=$admingroups mrmoduleactive=$mrmoduleactive}
    {/if}
{/foreach}
{elseif $query}
    <div class="no-results">{str tag=nosearchresultsfound section=group}</div>
{/if}
