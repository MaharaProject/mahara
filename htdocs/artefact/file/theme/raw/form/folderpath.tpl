{foreach from=$path item=f name=path}
  {if !$smarty.foreach.path.first}/ {/if}<a href="{$querybase}folder={$f->id}" class="changefolder">{$f->title|escape|str_shorten:34}</a>
{/foreach}
