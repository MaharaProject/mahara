{if $data}
{foreach from=$data item=user}
    {include file="user/userfind.tpl" user=$user page=$page admingroups=$admingroups mrmoduleactive=$mrmoduleactive}
{/foreach}
{elseif $query}
    <div class="no-results">{str tag=nosearchresultsfound section=group}</div>
{/if}
