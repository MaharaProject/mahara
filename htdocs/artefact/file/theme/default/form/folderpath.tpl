{foreach from=$path item=f name=path}
  {if !$smarty.foreach.path.first}/ {/if}
    <button type="submit" class="changefolder link" name="changefolder[{$f->id}]" title="{str tag=gotofolder section=artefact.file arg1=$f->title}" value="{$f->id}">{$f->title|str_shorten:34}</button>
<!--a href="?folder={$f->id}{$queryparams}" class="changefolder">{$f->title}</a-->
{/foreach}
