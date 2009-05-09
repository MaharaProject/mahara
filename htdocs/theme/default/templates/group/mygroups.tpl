{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
<div class="addgrouplink">
<span class="addicon fr">
<a href="{$WWWROOT}group/create.php" id="btn-creategroup">{str tag="creategroup" section="group"}</a>
</span>
</div>
{$form}
{if $groups}
<table id="mygroupstable" class="fullwidth">
{foreach from=$groups item=group}
<tr class="r{cycle values=0,1}">
<td>
{include file="group/group.tpl" group=$group returnto='mygroups'}
</td>
</tr>
{/foreach}
</table>
{$pagination}
{else}
<div class="message">
{str tag="trysearchingforgroups" section="group" args=$searchingforgroups}
</div>
{/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
