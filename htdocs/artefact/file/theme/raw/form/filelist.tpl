{auto_escape off}
{if !$filelist}
<p>{str tag=nofilesfound section=artefact.file}</p>
{else}
<table id="filelist" class="tablerenderer filelist">
 <thead>
  <tr>
   <th></th>
   <th>{str tag=Name section=artefact.file}</th>
   <th>{str tag=Description section=artefact.file}</th>
  {if !$showtags && !$editmeta}
   <th>{str tag=Size section=artefact.file}</th>
   <th>{str tag=Date section=artefact.file}</th>
  {/if}
  {if $showtags}
   <th>{str tag=tags}</th>
  {/if}
  {if $editmeta}
   <th></th>
  {/if}
   <th></th>
  </tr>
 </thead>
 <tbody>
  {foreach from=$filelist item=file}
    {if !$publishing || !$file->permissions || $file->can_republish}{assign var=publishable value=1}{else}{assign var=publishable value=0}{/if}
  <tr id="file:{$file->id}" class="{cycle values='r0,r1'} directory-item{if $file->isparent} parentfolder{/if}{if $file->artefacttype == 'folder'} folder{/if}{if $highlight && $highlight == $file->id} highlight-file{/if}{if $edit == $file->id} hidden{/if}{if !$publishable && $file->artefacttype != 'folder'} disabled{/if}" {if !$publishable && $file->artefacttype != 'folder'} title="{str tag=notpublishable section=artefact.file}"{/if}>
    <td>
      {if $editable}
      <div{if !$file->isparent} class="icon-drag" id="drag:{$file->id}"{/if}>
        <img src="{$file->icon}"{if !$file->isparent} title="{str tag=clickanddragtomovefile section=artefact.file arg1=$file->title}"{/if}>
      </div>
      {else}
        <img src="{$file->icon}">
      {/if}
    </td>
    <td class="filename">
    {assign var=displaytitle value=$file->title|str_shorten_text:34|escape}
    {if $file->artefacttype == 'folder'}
      <a href="{$querybase}folder={$file->id}{if $owner}&owner={$owner}{if $ownerid}&ownerid={$ownerid}{/if}{/if}" class="changefolder" title="{str tag=gotofolder section=artefact.file arg1=$displaytitle}">{$displaytitle}</a>
    {elseif !$publishable}
      {$displaytitle}
    {else}
      <a href="{$WWWROOT}artefact/file/download.php?file={$file->id}" target="_blank" title="{str tag=downloadfile section=artefact.file arg1=$displaytitle}">{$displaytitle}</a>
    {/if}
    </td>
    <td>{$file->description|escape}</td>
    {if !$showtags && !$editmeta}
    <td>{tif $file->size ?: ''}</td>
    <td>{tif $file->mtime ?: ''}</td>
    {/if}
    {if $showtags}
    <td>{if $file->tags}<span class="tags">{list_tags tags=$file->tags owner=$showtags}</span>{/if}</td>
    {/if}
    {if $editmeta}
    <td>
      {if !$file->isparent}
        {if !isset($file->can_edit) || $file->can_edit !== 0}<input type="submit" class="tag-edit submit" name="{$prefix}_edit[{$file->id}]" value="{str tag=edit}" />{/if}
      {/if}
    </td>
    {/if}
    <td class="right">
    {if $editable && !$file->isparent}
      {if $file->artefacttype == 'archive'}<a href="{$WWWROOT}artefact/file/extract.php?file={$file->id}">{str tag=Unzip section=artefact.file}</a>{/if}
      {if !isset($file->can_edit) || $file->can_edit !== 0}<input type="submit" class="submit btn-edit s" name="{$prefix}_edit[{$file->id}]" value="{str tag=edit}" />
      <input type="submit" class="submit btn-del s" name="{$prefix}_delete[{$file->id}]" value="{str tag=delete}" />{/if}
    {/if}
    {if $selectable && ($file->artefacttype != 'folder' || $selectfolders) && $publishable && !$file->isparent}
      <input type="submit" class="select small" name="{$prefix}_select[{$file->id}]" id="{$prefix}_select_{$file->id}" value="{str tag=select}" />
    {/if}
    </td>
  </tr>
  {if $edit == $file->id}
    {include file="artefact:file:form/editfile.tpl" prefix=$prefix fileinfo=$file groupinfo=$groupinfo}
  {/if}
  {/foreach}
 </tbody>
</table>
{/if}
{/auto_escape}
