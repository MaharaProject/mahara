{if $added}{str tag=numbernewusersadded section=admin arg1=$added}{else}{str tag=nousersadded section=admin}{/if}
{str tag=numberusersupdated section=admin arg1=count($updates)}
<a href="" onclick="toggleElementClass('hidden', 'csvupdateinfo'); return false;">{str tag=showupdatedetails section=admin}</a>
<div id="csvupdateinfo" class="hidden">
{foreach from=$updates key=username item=fields}{strip}
  <div>&nbsp;{$username}:&nbsp;
  {foreach from=$fields key=k item=v name=fields}
    {$k} &rarr; {$v}{if !$dwoo.foreach.fields.last},&nbsp;{/if}
  {/foreach}
  </div>{/strip}
{/foreach}
</div>
