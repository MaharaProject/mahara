{include file="header.tpl"}
<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
    		<h2>{str tag="mygroups"}</h2>

    		<div id="mygroups"><a href="create.php" id="addnewgroup">{str tag="creategroup"}</a>

			<table id="grouplist">
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
			
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
