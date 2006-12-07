{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
			<h2>{str section="artefact.file" tag="myfiles"}</h2>
				<div id="uploader"></div>
				<div id="foldernav"></div>
			<table id="filelist">
				<thead><tr>
					<th>Name</th>
					<th>Size</th>
					<th>Time</th>
					<th></th>
				</tr></thead>
				<tbody><tr><th></th></tr></tbody>
			</table>
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
