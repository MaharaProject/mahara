{include file="header.tpl"}
{include file="sidebar.tpl"}

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
			</select>{contextualhelp plugintype='core' pluginname='activity' section='activitytypeselect'}
			</form>
			<form method="post" onSubmit="{$markread}">
			<table id="activitylist">
				<thead>
					<tr>
						<th>{str section='activity' tag='subject'}</th>
						<th>{str section='activity' tag='type'}</th>
						<th>{str section='activity' tag='date'}</th>
						<th>{str section='activity' tag='read'}<br><a href="" onclick="{$selectall}" class="s">{str section='activity' tag='selectall'}</a></th>
					</tr>
				</thead>
				<tbody>
			
				</tbody>
				<tfoot>
					<tr>
						<td colspan="5" class="markasreadtd"><div class="markasread"><input class="submit" type="submit" value="{str tag='markasread' section='activity'}" /></div><div id="messagediv"></div></td>
					</tr>
				</tfoot>
			</table>
			</form>
			</div>
			
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
