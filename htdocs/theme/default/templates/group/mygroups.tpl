{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
<h2>{str tag="mygroups"}</h2>
<div class="addgrouplink">
<span class="addicon">
<a href="{$WWWROOT}group/create.php">Add New Group</a>
</span>
</div>
{$form}
{if $groups}
<table>
{foreach from=$groups item=group name=groups}
{if $smarty.foreach.groups.index % 2 == 0}<tr class=r0>
{else}<tr class=r1>
{/if}
<td>
{include file="group/group.tpl" group=$group returnto='mygroups'}
</td>
</tr>
{/foreach}
</table>
<div class="center">{$pagination}</div>
{else}
<div class="message">
{str tag="trysearchingforgroups" args=$searchingforgroups}
</div>
{/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
