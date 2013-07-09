<div>
  {if !$simpledisplay}<h3 class="title">{$title}</h3>{/if}
  <div class="detail">{$description}</div>
  {if $tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$owner tags=$tags}</div>{/if}
  <div id="commentfiles">
  {if (isset($children))}
  <h4>{str tag=contents section=artefact.file}:</h4>
  <table class="fullwidth">
    <thead>
      <tr>
        <th></th>
        <th class="filename">{str tag=name}</th>
        <th class="filedescription">{str tag=description}</th>
        {if !$simpledisplay}<th class="filedate">{str tag=Date section=artefact.file}</th>{/if}
      </tr>
    </thead>
    <tbody>
    {foreach from=$children item=child}
      <tr class="{cycle values='r0,r1'}">
        <td class="icon-container"><img src="{$child->iconsrc}" border="0" alt="{$child->artefacttype}"></td>
        <td class="filename"><a href="{$WWWROOT}view/artefact.php?artefact={$child->id}&amp;view={$viewid}" title="{$child->hovertitle}">{$child->title}</a></td>
        <td class="filedescription">{$child->description}</td>
        {if !$simpledisplay}
		<td class="filedate">{$child->date}</td>
		{/if}
      </tr>
    {/foreach}
    </tbody></table>
  {else}
    {str tag=emptyfolder section=artefact.file}
  {/if}
  </div>
</div>

