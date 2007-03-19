{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
    		<h2>{str tag="mygroups"}</h2>

    		<div id="mygroups">
		
				<div id="addnewgroupdiv">
                                  <span id="addnewgroup">
                                        <a href="create.php" id="addnewgroup">{str tag="creategroup"}</a>
                                  </span>
                                  {contextualhelp plugintype='core' pluginname='groups' section='addgroupbutton'}
                                </div>

			<table id="grouplist" class="hidden tablerenderer">
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
