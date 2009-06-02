{foreach from=$path item=f name=path}
  {if !$smarty.foreach.path.first}/ {/if}<a href="{$querybase}folder={$f->id}" class="changefolder">{$f->title|str_shorten_text:34|escape}</a>
{/foreach}
