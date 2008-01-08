{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading}</h2>
{$form}

{if $groups}
<table>
{foreach from=$groups item=group name=groups}
{if $smarty.foreach.groups.index % 2 == 0}<tr class=r0>
{else}<tr class=r1>
{/if}
<td>
{include file="group/group.tpl" group=$group returnto='find'}
</td>
</tr>
{/foreach}
</table>

<div class="center">{$pagination}</div>
{else}
<div class="message">
{str tag="nogroupsfound"}
</div>
{/if}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
