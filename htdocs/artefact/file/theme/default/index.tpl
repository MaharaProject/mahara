{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
			<h2>{str section="artefact.file" tag="myfiles"}</h2>
			<div id="myfiles">
				<div id="uploader"></div>
			<table id="filelist">
				<thead><tr>
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
