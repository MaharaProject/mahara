{include file="header.tpl"}
{if $institution}
  {$institutionselector|safe}
{/if}

{if $institution && $institution == 'mahara'}
            <p class="intro">{str tag="adminfilespagedescription" section="admin" args=$descriptionstrargs}</p>
{else}
			<p class="intro">{str tag='fileinstructions' section='artefact.file'}</p>
{/if}
			<div>{$form|safe}</div>
{include file="footer.tpl"}

