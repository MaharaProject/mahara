{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

{$form}

{if $groups}
<table id="findgroups" class="fullwidth">
{foreach from=$groups item=group name=groups}
<tr class="r{cycle values=0,1}">
<td>
{include file="group/group.tpl" group=$group returnto='find'}
</td>
</tr>
{/foreach}
</table>

{$pagination}
{else}
<div class="message">
{str tag="nogroupsfound" section="group"}
</div>
{/if}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
