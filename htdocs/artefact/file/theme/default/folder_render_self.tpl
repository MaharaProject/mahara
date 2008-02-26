<div>
  {if !$simpledisplay}<h3>{$title|escape}</h3>{/if}
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
        {if !$simpledisplay}<th>{str tag=Date section=artefact.file}</th>{/if}
      </tr>
    </thead>
    <tbody>
    {foreach from=$children item=child}
      <tr class="{cycle values=r1,r0}">
        <td><img src="{$child->iconsrc}" border="0" alt="{$child->artefacttype|escape}"></td>
        <td><a href="{$WWWROOT}view/artefact.php?artefact={$child->id|escape}&amp;view={$viewid|escape}" title="{$child->hovertitle}">{$child->title}</a></td>
        <td>{$child->description}</td>
        {if !$simpledisplay}<td>{$child->date}</td>{/if}
      </tr>
    {/foreach}
    </tbody></table>
  {else}
    {str tag=emptyfolder section=artefact.file}
  {/if}
  </div>
</div>
