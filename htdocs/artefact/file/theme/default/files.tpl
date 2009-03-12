{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
{if $institution}
  {$institutionselector}
{/if}

{if $institution && $institution == 'mahara'}
            <p>{str tag="adminfilespagedescription" section="admin" args=$descriptionstrargs}</p>
{else}
			{str tag='fileinstructions' section='artefact.file'}
{/if}
			<div>{$form}</div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
