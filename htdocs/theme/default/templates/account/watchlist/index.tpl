{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
	
			<h2>{str tag="watchlist"}</h2>
			<div id="mywatchlist">
			<form method="post">
			{str tag='filter'}:
			<select id="type" name="type" onChange="statusChange(); return false;">
				<option value="views">{str section='activity' tag='views'}</option>
				<option value="artefacts">{str section='activity' tag='artefacts'}</option>
				<option value="communities">{str section='activity' tag='communities'}</option>
			</select>
			{str tag='belongingto'}:
			<select id="user" name="user" onChange="statusChange(); return false;">
				<option>{str tag='allusers'}</option>
			{foreach from=$viewusers item='user}
				<option value="{$user->id}">{display_name user=$user}</option>
			{/foreach}
			</select>
			</form>
			<p><b><div id="typeheader">{str section='activity' tag='monitored'} {$typestr}</div></b></p>
			<form method="post" onSubmit="{$stopmonitoring}">
			<table id="watchlist">
				<thead>
					<tr>
						<th></th>
						<th>[<a href="" onClick="{$selectall}">{str section='activity' tag='selectall'}</a>]</th>
						<th id="recurseheader">{$recursestr}</th>
					</tr>
				</thead>
				<tbody>
			
				</tbody>
				<tfoot>
					<tr>
						<td align="right" colspan="4"><div id="messagediv"></div></td>
						<td align="right"><input type="submit" value="{str tag='stopmonitoring' section='activity'}" /></td>
					</tr>
				</tfoot>
			</table>
			</form>
			</div>
			
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
