{include file="header.tpl"}
{if $institution}
  {$institutionselector}
{/if}

{if $institution && $institution == 'mahara'}
            <p>{str tag="adminfilespagedescription" section="admin" args=$descriptionstrargs}</p>
{else}
			<p>{str tag='fileinstructions' section='artefact.file'}</p>
{/if}
			<div>{$form}</div>
{include file="footer.tpl"}
