{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
			<h2>{str section="admin" tag="adminfiles"}</h2>
			<div id="myfiles">
				<div id="uploader"></div>
			<table id="filelist" class="hidden tablerenderer">
				<thead><tr>
					<th></th>
					<th>{str tag=name section=artefact.file}</th>
					<th>{str tag=description section=artefact.file}</th>
					<th>{str tag=size section=artefact.file}</th>
					<th>{str tag=date}</th>
					<th></th>
				</tr></thead>
				<tbody><tr><th></th></tr></tbody>
			</table>
			</div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
