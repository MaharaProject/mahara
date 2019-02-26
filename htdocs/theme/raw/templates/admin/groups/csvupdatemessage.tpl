{if $added}{str tag=numbernewgroupsadded section=admin arg1=$added}{else}{str tag=nogroupsadded section=admin}{/if}
{str tag=numbergroupsupdated section=admin arg1=count($updates)}
<a href="" onclick="jQuery('#csvupdateinfo').toggleClass('d-none'); return false;">{str tag=showupdatedetails section=admin}</a>
<div id="csvupdateinfo" class="d-none">
{foreach from=$updates key=shortname item=fields}{strip}
  <div>&nbsp;{$shortname}:&nbsp;
  {foreach from=$fields key=k item=v name=fields}
    {$k} &rarr; {$v}{if !$dwoo.foreach.fields.last},&nbsp;{/if}
  {/foreach}
  </div>{/strip}
{/foreach}
</div>
