{include file="header.tpl"}
<div id="column-right">
{include file="adminmenu.tpl"}
</div>
{include file="columnleftstart.tpl"}
    		<h2>{str tag="mygroups"}</h2>

    		<div id="mygroups">
		
				<div id="addnewgroupdiv"><a href="create.php" id="addnewgroup">{str tag="creategroup"}</a></div>{contextualhelp plugintype='core' pluginname='groups' section='addgroupbutton'}

			<table id="grouplist" class="tablerenderer">
				<thead>
					<tr>
						<th>{str tag="groupname"}</th>
						<th>{str tag="membercount"}{contextualhelp plugintype='core' pluginname='groups' section='groupmembercountheader'}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			</div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
