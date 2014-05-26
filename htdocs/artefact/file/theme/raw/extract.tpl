{include file="header.tpl"}

{if $file}
  <h5>{$file->get('title')}</h5>
  {if $zipinfo}
  <p>
      <span><strong>{str tag=Files section=artefact.file}:</strong> {$zipinfo->files}&nbsp;</span>
      <span><strong>{str tag=Folders section=artefact.file}:</strong> {$zipinfo->folders}</span>
      <span><strong>{str tag=spacerequired section=artefact.file}:</strong> {$zipinfo->displaysize}</span>
  </p>
  {/if}
  <p>{$message}</p>
  {if $zipinfo}
  {$form|safe}
  <p>
      <div><strong>{str tag=Contents section=artefact.file}:</strong></div>
{foreach from=$zipinfo->names item=name}
      <div>{$name}</div>
{/foreach}
  </p>
  {/if}
{/if}

{include file="footer.tpl"}
