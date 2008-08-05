{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
{if $groupid}
  <h2>{$heading}</h2>
  {include file="group/tabstart.tpl" current="files"}
{/if}
			{str tag='fileinstructions' section='artefact.file'}
			<div id="myfiles">
				<div id="uploader"></div>
			<table id="filelist" class="hidden tablerenderer">
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
{if $groupid}{include file="group/tabend.tpl"}{/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
