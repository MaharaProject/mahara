{if $page != 0}
  <span class="search-results-page prev"><a href="{$url}&amp;offset={$limit*$prev}">{str tag=prevpage}</a></span>
{/if}
{foreach from=$pagenumbers item=i name=pagenumbers}
  {if !$smarty.foreach.pagenumbers.first && $prevpagenum < $i-1}...{/if}
  <span class="search-results-page{if $i == $page} selected{/if}"><a href="{$url}&amp;offset={$i*$limit}">{$i+1}</a></span>
  {assign var='prevpagenum' value=$i}
{/foreach}
{if $page < $pages - 1}
  <span class="search-results-page next"><a href="{$url}&amp;offset={$limit*$next}">{str tag=nextpage}</a></span>
{/if}

