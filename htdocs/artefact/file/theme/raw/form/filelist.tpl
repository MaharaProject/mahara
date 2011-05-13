{if !$filelist}
<p>{str tag=nofilesfound section=artefact.file}</p>
{else}
<table id="{$prefix}_filelist" class="tablerenderer filelist">
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
   <th><div>{str tag=tags}</div></th>
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
    <td class="filethumb">
      {if $editable}
      <div{if !$file->isparent} class="icon-drag" id="drag:{$file->id}"{/if}>
        <img src="{$file->icon}"{if !$file->isparent} title="{str tag=clickanddragtomovefile section=artefact.file arg1=$file->title}"{/if}>
      </div>
      {else}
        <img src="{$file->icon}">
      {/if}
    </td>
    <td class="filename">
    {assign var=displaytitle value=$file->title|str_shorten_text:34|safe}
    {if $file->artefacttype == 'folder'}
      <a href="{$querybase}folder={$file->id}{if $owner}&owner={$owner}{if $ownerid}&ownerid={$ownerid}{/if}{/if}" class="changefolder" title="{str tag=gotofolder section=artefact.file arg1=$displaytitle}">{$displaytitle}</a>
    {elseif !$publishable}
      {$displaytitle}
    {else}
      <a href="{$WWWROOT}artefact/file/download.php?file={$file->id}" target="_blank" title="{str tag=downloadfile section=artefact.file arg1=$displaytitle}">{$displaytitle}</a>
    {/if}
    </td>
    <td class="filedescription">{$file->description}</td>
    {if !$showtags && !$editmeta}
    <td class="filesize">{tif $file->size ?: ''}</td>
    <td class="filedate">{tif $file->mtime ?: ''}</td>
    {/if}
    {if $showtags}
    <td class="filetags">{if $file->tags}<span>{list_tags tags=$file->tags owner=$showtags}</span>{/if}</td>
    {/if}
    {if $editmeta}
    <td class="right s">
      {if $file->locked}
        <span class="s dull">{str tag=Submitted section=view}</span>
      {elseif !$file->isparent}
        {if !isset($file->can_edit) || $file->can_edit !== 0}<input type="submit" class="icon btn-edit tag-edit submit" name="{$prefix}_edit[{$file->id}]" value="{str tag=edit}" />{/if}
      {/if}
    </td>
    {/if}
    <!-- Ensure space for 3 buttons (in the case of a really long single line string in a user input field -->
    <td class="right s btns3">
    {if $editable && !$file->isparent}
      {if $file->artefacttype == 'archive'}<a href="{$WWWROOT}artefact/file/extract.php?file={$file->id}"><img src="{theme_url filename="images/unzip.gif"}" alt="{str tag=Unzip section=artefact.file}"/></a>{/if}
      {if $file->locked}
        <span class="s dull">{str tag=Submitted section=view}</span>
      {elseif !isset($file->can_edit) || $file->can_edit != 0}
        <input type="image" src="{theme_url filename="images/edit.gif"}" name="{$prefix}_edit[{$file->id}]" value="" title="{str tag=edit}"/>
        <input type="image" src="{theme_url filename="images/icon_close.gif"}" name="{$prefix}_delete[{$file->id}]" value="" title="{str tag=delete}"/>
      {/if}
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
