<div>
  {if !$simpledisplay}<h3>{$title}</h3>{/if}
  <p>{$description}</p>
  {if $tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$owner tags=$tags}</div>{/if}
  <div id="commentfiles">
  {if (isset($children))}
  <h3>{str tag=contents section=artefact.file}:</h3>
  <table class="fullwidth">
    <thead>
      <tr>
        <th></th>
        <th>{str tag=name}</th>
        <th>{str tag=description}</th>
        {if !$simpledisplay}<th>{str tag=Date section=artefact.file}</th>{/if}
      </tr>
    </thead>
    <tbody>
    {foreach from=$children item=child}
      <tr class="{cycle values='r0,r1'}">
        <td class="iconcell"><img src="{$child->iconsrc}" border="0" alt="{$child->artefacttype}"></td>
        <td><a href="{$WWWROOT}view/artefact.php?artefact={$child->id}&amp;view={$viewid}" title="{$child->hovertitle}">{$child->title}</a></td>
        <td class="s">{$child->description}</td>
        {if !$simpledisplay}
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

