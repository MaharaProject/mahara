<div>
  {if !$hidetitle}<h3>{$title|escape}</h3>{/if}
  <div>{$description|escape}</div>
  <div>
  {if (isset($children))}
    <table>
    <thead>
      <tr>
        <th colspan="5">{str tag=contents section=artefact.file}:</th>
      </tr>
      <tr>
        <th></th>
        <th>{str tag=name}</th>
        <th>{str tag=description}</th>
        <th>{str tag=Date section=artefact.file}</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$children item=child}
      <tr class="{cycle values=r1,r0}">
        <td><img src="{$child->iconsrc}" border="0" alt="{$child->artefacttype|escape}"></td>
        <td><a href="{$WWWROOT}view/artefact.php?artefact={$child->id|escape}&amp;view={$viewid|escape}">{$child->title}</a></td>
        <td>{$child->description}</td>
        <td>{$child->date}</td>
      </tr>
    {/foreach}
    </tbody></table>
  {else}
    {str tag=emptyfolder section=artefact.file}
  {/if}
  </div>
</div>
