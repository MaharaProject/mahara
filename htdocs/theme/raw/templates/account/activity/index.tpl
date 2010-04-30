{include file="header.tpl"}

			<div id="notifications">
			<form method="post">
			<label>{str section='activity' tag='type'}:</label>
			<select name="type" onChange="{$typechange}">
			{foreach from=$options item=name key=type}
				<option value="{$type}">{$name}</option>
			{/foreach}
			</select>{contextualhelp plugintype='core' pluginname='activity' section='activitytypeselect'}
			</form>
			<form name="notificationlist" method="post" onSubmit="{$markread}">
			<table id="activitylist" class="fullwidth">
				<thead>
					<tr>
						<th>{str section='activity' tag='subject'}</th>
						<th>{str section='activity' tag='type'}</th>
						<th>{str section='activity' tag='date'}</th>
						<th width="50" class="center">{str section='activity' tag='read'}<br><a href="" onclick="{$selectallread}">{str section='activity' tag='selectall'}</a></th>
						<th width="50" class="center">{str tag='delete'}<br><a href="" onclick="{$selectalldel}">{str section='activity' tag='selectall'}</a></th>
					</tr>
				</thead>
                <tfoot>
				  	<tr>
						<td colspan="5" class="right">
						  <input class="submit" type="submit" value="{str tag='markasread' section='activity'}" />
						  <input class="submit btn-delete" type="button" value="{str tag='delete'}" onClick="{$markdel}" />
						</td>
				  	</tr>
				</tfoot>
				<tbody></tbody>
			</table>
			</form>
			</div>
            {$deleteall}
			
{include file="footer.tpl"}
