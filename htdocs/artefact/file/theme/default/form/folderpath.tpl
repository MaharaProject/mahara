{foreach from=$path item=f name=path}
  {if !$smarty.foreach.path.first}/ {/if}<a href="?folder={$f->id}{$queryparams}" class="changefolder">{$f->title}</a>
{/foreach}
