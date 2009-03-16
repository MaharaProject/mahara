{if empty($filelist)}
<p>{str tag=nofilesfound section=artefact.file}</p>
{else}
<table id="filelist" class="tablerenderer">
 <thead>
  <tr>
   <th></th>
   <th>{str tag=Name section=artefact.file}</th>
   <th>{str tag=Description section=artefact.file}</th>
   <th>{str tag=Size section=artefact.file}</th>
   <th>{str tag=Date section=artefact.file}</th>
   <th></th>
  </tr>
 </thead>
 <tbody>
  {foreach from=$filelist item=file}
  <tr id="file:{$file->id}" class="r{cycle values=0,1} directory-item{if $file->isparent} parentfolder{/if}{if $file->artefacttype == 'folder'} folder{/if}{if !empty($highlight) && $highlight == $file->id} highlight-file{/if}{if $edit == $file->id} hidden{/if}">
    <td>
      <div{if !$file->isparent} class="icon-drag"{/if}>
        <img src="{if $file->artefacttype == 'image'}{$WWWROOT}artefact/file/download.php?file={$file->id}&size=20x20{else}{$THEMEURL}images/{$file->artefacttype}.gif{/if}"{if !$file->isparent} title="{str tag=clickanddragtomovefile section=artefact.file arg1=$file->title}"{/if}>
      </div>
    </td>
    <td class="filename">
    {if $file->artefacttype == 'folder'}
      <a href="?folder={$file->id}{$queryparams}" class="changefolder" title="{str tag=gotofolder section=artefact.file arg1=$file->title}">{$file->title|str_shorten:34}</a>
    {else}
      <a href="{$WWWROOT}artefact/file/download.php?file={$file->id}" title="{str tag=downloadfile section=artefact.file arg1=$file->title}">{$file->title|str_shorten:34}</a>
    {/if}
    </td>
    <td>{$file->description}</td>
    <td>{$file->size}</td>
    <td>{$file->mtime}</td>
    <td>
    {if $config.edit && !$file->isparent}
      {if !isset($file->can_edit) || $file->can_edit !== 0}<button type="submit" name="edit[{$file->id}]" value="{$file->id}">{str tag=edit}</button>{/if}
      {if $file->childcount == 0}<button type="submit" name="delete[{$file->id}]" value="{$file->id}">{str tag=delete}</button>{/if}
    {/if}
    </td>
  </tr>
  {if $edit == $file->id}
    {include file="artefact:file:form/editfile.tpl" fileinfo=$file}
  {/if}
  {/foreach}
 </tbody>
</table>
{/if}
