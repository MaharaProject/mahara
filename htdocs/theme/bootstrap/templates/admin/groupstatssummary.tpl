{if !$grouptypecounts}
<p>{str tag=nogroups section=group}</p>
{else}
<p>{str tag=groupcountsbytype section=admin}:
<ul>
{foreach from=$grouptypecounts item=item}
  <li>{str tag=name section=grouptype.$item->grouptype}: {$item->groups}</li>
{/foreach}
</ul>
</p>
<p>{str tag=groupcountsbyjointype section=admin}:
<ul>
{foreach from=$jointypecounts item=item}
  <li>{str tag=membershiptype.$item->jointype section=group}: {$item->groups}</li>
{/foreach}
</ul>
</p>
{if $groupgraph}
  <img src="{$groupgraph}" alt="" />
{/if}
{/if}