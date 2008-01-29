{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}	
			<h2>{str tag="notifications"}</h2>
			
			<div id="notifications">
			<form method="post">
			{str section='activity' tag='type'}:
			<select name="type" onChange="{$typechange}">
				<option value="all">{str section='activity' tag='alltypes'}</option>
			{foreach from=$types item=name key=type}
				<option value="{$type}">{$name}</option>
			{/foreach}
			</select>{contextualhelp plugintype='core' pluginname='activity' section='activitytypeselect'}
			</form>
			<form name="notificationlist" method="post" onSubmit="{$markread}">
			<table id="activitylist">
				<thead>
					<tr>
						<th>{str section='activity' tag='subject'}</th>
						<th>{str section='activity' tag='type'}</th>
						<th>{str section='activity' tag='date'}</th>
						<th>{str section='activity' tag='read'}<br><a href="" onclick="{$selectallread}" class="s">{str section='activity' tag='selectall'}</a></th>
						<th>{str tag='delete'}<br><a href="" onclick="{$selectalldel}" class="s">{str section='activity' tag='selectall'}</a></th>
					</tr>
				</thead>
				<tbody>
			
				</tbody>
                                <tfoot>
  <tr><td colspan="5" class="markasreadtd">
    <div class="markasread">
      <input class="submit" type="submit" value="{str tag='markasread' section='activity'}" />
      <input class="submit" type="button" value="{str tag='delete'}" onClick="{$markdel}" />
    </div>
    <div id="messagediv"></div></td>
  </tr>
				</tfoot>
			</table>
			</form>
			</div>
			
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
