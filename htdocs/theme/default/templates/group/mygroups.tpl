{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
<h2>{str tag="mygroups"}</h2>
<div class="addgrouplink">
<span class="addicon">
<a href="{$WWWROOT}group/create.php">{str tag="addgroup" section="group"}</a>
</span>
</div>
{$form}
{if $groups}
<table>
{foreach from=$groups item=group}
<tr class="r{cycle values=0,1}">
<td>
{include file="group/group.tpl" group=$group returnto='mygroups'}
</td>
</tr>
{/foreach}
</table>
<div class="center">{$pagination}</div>
{else}
<div class="message">
{str tag="trysearchingforgroups" section="group" args=$searchingforgroups}
</div>
{/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
