{include file="header.tpl"}
<div id="column-right">
{include file="adminmenu.tpl"}
</div>
{include file="columnleftstart.tpl"}
    		<h2>{str tag="mygroups"}</h2>

    		<div class="addicon"><a href="create.php">{str tag="creategroup"}</a>

			<table id="grouplist" class="tablerenderer">
				<thead>
					<tr>
						<th>{str tag="groupname"}</th>
						<th>{str tag="membercount"}</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			</div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
