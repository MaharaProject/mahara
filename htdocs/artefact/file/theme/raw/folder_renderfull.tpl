<div>
  <h3>{$artefact->get('title')|escape}</h3>
  <div>{$artefact->get('description')}</div>
  <div>
  {if (isset($children))}
    <table>
    <thead>
      <tr>
        <th colspan=5>{str tag=contents section=artefact.file}:</th>
      </tr>
      <tr>
        {if (isset($options.icon))}
        <th></th>
        {/if}
        <th>{str tag=name}</th>
        <th>{str tag=description}</th>
        {if (isset($options.date))}
        <th>{str tag=Date section=artefact.file}</th>
        {/if}
      </tr>
    </thead>
    <tbody>
    {foreach from=$children item=child}
      <tr class="{cycle values=r1,r0}">
        {if (isset($options.icon))}
        <td><img src="{$child->iconsrc}" border="0" alt="{$child->artefacttype}"></td>
        {/if}
        <td>{$child->title}</td>
        <td>{$child->description}</td>
        {if (isset($options.date))}
        <td>{$child->date}</td>
        {/if}
      </tr>
    {/foreach}
    </tbody></table>
  {else}
    {str tag=emptyfolder section=artefact.file}
  {/if}
  </div>
</div>
