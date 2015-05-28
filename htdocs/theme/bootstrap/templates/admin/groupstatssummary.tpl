{if !$grouptypecounts}
<p class="lead text-small">{str tag=nogroups section=group}</p>
{else}

{if $groupgraph}
  <img src="{$groupgraph}" alt="" class="pull-right" />
{/if}
<div>
<h4>{str tag=groupcountsbytype section=admin}:</h4>

    <ul>
    {foreach from=$grouptypecounts item=item}
      <li class="">{str tag=name section=grouptype.$item->grouptype}: {$item->groups}</li>
    {/foreach}
    </ul>
</div>
<div>
<h4>{str tag=groupcountsbyjointype section=admin}:</h4>
    <ul>
    {foreach from=$jointypecounts item=item}
      <li>{str tag=membershiptype.$item->jointype section=group}: {$item->groups}</li>
    {/foreach}
    </ul>
</div>

{/if}
