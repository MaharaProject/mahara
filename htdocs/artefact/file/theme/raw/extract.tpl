{include file="header.tpl"}

{if $file}
  <h5>{$file->get('title')|escape}</h5>
  <p>
      <span><label>{str tag=Files section=artefact.file}:</label> {$zipinfo->files}&nbsp;</span>
      <span><label>{str tag=Folders section=artefact.file}:</label> {$zipinfo->folders}</span>
      <span><label>{str tag=spacerequired section=artefact.file}:</label> {$zipinfo->displaysize}</span>
  </p>
  <p>{$message|escape}</p>
  {$form}
  <p>
      <div><label>{str tag=Contents section=artefact.file}:</label></div>
{foreach from=$zipinfo->names item=name}
      <div>{$name|escape}</div>
{/foreach}
  </p>
{/if}

{include file="footer.tpl"}
