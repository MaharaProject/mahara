{if $viewcount == 0}
<p>{str tag=noviews section=view}</p>
{/if}
{if $blocktypecounts}
<p>{str tag=blockcountsbytype section=admin}:
<ul>
{foreach from=$blocktypecounts item=item}
  <li>{str tag=title section=blocktype.$item->langsection}: {$item->blocks}</li>
{/foreach}
</ul>
</p>
{/if}
{if $viewtypes}
  <img src="{$viewtypes}" alt="" />
{/if}
