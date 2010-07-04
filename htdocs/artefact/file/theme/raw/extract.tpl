{include file="header.tpl"}

{if $file}
  <h5>{$file->get('title')}</h5>
  {if $zipinfo}
  <p>
      <span><label>{str tag=Files section=artefact.file}:</label> {$zipinfo->files}&nbsp;</span>
      <span><label>{str tag=Folders section=artefact.file}:</label> {$zipinfo->folders}</span>
      <span><label>{str tag=spacerequired section=artefact.file}:</label> {$zipinfo->displaysize}</span>
  </p>
  {/if}
  <p>{$message}</p>
  {if $zipinfo}
  {$form|safe}
  <p>
      <div><label>{str tag=Contents section=artefact.file}:</label></div>
{foreach from=$zipinfo->names item=name}
      <div>{$name}</div>
{/foreach}
  </p>
  {/if}
{/if}

{include file="footer.tpl"}
