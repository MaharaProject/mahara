{include file="header.tpl"}
{if $institution}
  {$institutionselector}
{/if}
			{str tag='fileinstructions' section='artefact.file'}
			<div id="myfiles">
				<div id="uploader"></div>
			<table id="filelist" class="hidden tablerenderer fullwidth">
				<thead><tr>
					<th></th>
					<th>{str tag=Name section=artefact.file}</th>
					<th>{str tag=Description section=artefact.file}</th>
					<th>{str tag=Size section=artefact.file}</th>
					<th>{str tag=Date section=artefact.file}</th>
					<th></th>
				</tr></thead>
				<tbody><tr><th></th></tr></tbody>
			</table>
			</div>
{include file="footer.tpl"}
