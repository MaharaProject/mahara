{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
			<h2>{str tag="watchlist"}</h2>
			<div id="mywatchlist">
			<form method="post">
			{str tag='filter'}:
			<select id="type" name="type" onChange="statusChange(); return false;">
				<option value="views">{str section='activity' tag='views'}</option>
				<option value="artefacts">{str section='activity' tag='artefacts'}</option>
				<option value="groups">{str section='activity' tag='groups'}</option>
			</select>
			{str tag='belongingto'}:
			<select id="user" name="user" onChange="statusChange(); return false;">
				<option>{str tag='allusers'}</option>
			{foreach from=$viewusers item='user}
				<option value="{$user->id}">{display_name user=$user}</option>
			{/foreach}
			</select>
			</form>
			<div id="typeheader">{str section='activity' tag='monitored'} {$typestr}</div>
	                <div id="typeandchildren">* {str section='activity' tag='andchildren}</div>
			<form method="post" onSubmit="{$stopmonitoring}">
			<table id="watchlist" class="hidden tablerenderer">
				<thead>
					<tr>
						<th></th>
						<th>[<a href="" onClick="{$selectall}">{str section='activity' tag='selectall'}</a>]
                            {contextualhelp plugintype='core' pluginname='activity' section='watchlistselectall'}

                        </th>
					</tr>
				</thead>
				<tbody>
			
				</tbody>
				<tfoot>
					<tr>
						<td colspan="3" class="stopmonitoringtd"><div class="stopmonitoring"><input type="submit" class="submit" value="{str tag='stopmonitoring' section='activity'}" /></div><div id="messagediv"></div></td>
					</tr>
				</tfoot>
			</table>
			</form>
			</div>
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
