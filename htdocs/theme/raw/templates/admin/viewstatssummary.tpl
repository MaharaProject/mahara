{if $viewcount == 0}
<p class="lead small-text">{str tag=noviews section=view}</p>
{/if}
{if $blocktypecounts}
<h4>{str tag=blockcountsbytype section=admin}: </h4>
{if $viewtypes}
  <img src="{$viewtypes}" alt="" class="pull-right" />
{/if}
<ul class="list-group unstyled pull-left">
{foreach from=$blocktypecounts item=item}
  <li class="list-group-item">{str tag=title section=blocktype.$item->langsection}: {$item->blocks}</li>
{/foreach}
</ul>

{/if}
