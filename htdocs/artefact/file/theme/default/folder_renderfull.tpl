<div>
  <h3>{$artefact->get('title')|escape}</h3>
  <div>{$artefact->get('description')}</div>
  {str tag=contents section=artefact.file}:
  <div>{$children}</div>
</div>
