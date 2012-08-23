{include file="header.tpl"}

			<div id="notifications">
			<form method="post">
			<label>{str section='activity' tag='type'}:</label>
			<select id="notifications_type" name="type">
				<option value="all">--</option>
			{foreach from=$options item=name key=t}
				<option value="{$t}"{if $type == $t} selected{/if}>{$name}</option>
			{/foreach}
			</select>{contextualhelp plugintype='core' pluginname='activity' section='activitytypeselect'}
			</form>
            
            {$deleteall|safe}
			<form name="notificationlist" method="post" onSubmit="markread(this, 'read'); return false;">
			<table id="activitylist" class="fullwidth">
				<thead>
					<tr>
						<th class="inboxicon"></th>
						<th>{str section='activity' tag='subject'}</th>
						<th>{str section='activity' tag='date'}</th>
						<th class="center">{str section='activity' tag='read'}<br><a href="" onclick="toggleChecked('tocheckread'); return false;">{str section='activity' tag='selectall'}</a></th>
						<th class="center">{str tag='delete'}<br><a href="" onclick="toggleChecked('tocheckdel'); return false;">{str section='activity' tag='selectall'}</a></th>
					</tr>
				</thead>
            <tfoot>
					  <tr>
						<td colspan="2"></td>
						<td colspan="3" class="right">
						  <input class="submit" type="submit" value="{str tag='markasread' section='activity'}" />
						  <input class="submit btn-del" type="button" value="{str tag='delete'}" onClick="markread(document.notificationlist, 'del'); return false;" />
						</td>
				  	</tr>
				</tfoot>
				<tbody>
                {$activitylist.tablerows|safe}
                </tbody>
			</table>
            {$activitylist.pagination|safe}
			</form>
			</div>
			
{include file="footer.tpl"}
