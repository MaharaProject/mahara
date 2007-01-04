{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}	
			<h2>{str tag="activity"}</h2>
			
			<div id="recentactivity">
			<form method="post">
			{str section='activity' tag='type'}:
			<select name="type" onChange="{$typechange}">
				<option value="all">{str section='activity' tag='alltypes'}</option>
			{foreach from=$types item='type'}
				{assign var="tag1" value=$type->name}
				{assign var="tag" value=type$tag1}
				<option value="{$type->name}">{str section='activity' tag=$tag}</option>
			{/foreach}
			</select>
			</form>
			<form method="post" onSubmit="{$markread}">
			<table id="activitylist">
				<thead>
					<tr>
						<th></th>
						<th>{str section='activity' tag='type'}</th>
						<th>{str section='activity' tag='date'}</th>
						<th>{str section='activity' tag='read'}</th>
						<th>[<a href="" onClick="{$selectall}">{str section='activity' tag='selectall'}</a>]</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
			
				</tbody>
				<tfoot>
					<tr>
						<td align="right" colspan="4"><div id="messagediv"></div></td>
						<td align="right"><input type="submit" value="{str tag='markasread' section='activity'}" /></td>
					</tr>
				</tfoot>
			</table>
			</form>
			</div>
			
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
