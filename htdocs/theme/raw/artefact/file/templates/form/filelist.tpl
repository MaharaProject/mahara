{if !$filelist}
<p>{str tag=nofilesfound section=artefact.file}</p>
{else}
<table id="{$prefix}_filelist" class="tablerenderer filelist fullwidth">
 <thead>
  <tr>
   <th></th>
   <th>{str tag=Name section=artefact.file}</th>
   <th>{str tag=Description section=artefact.file}</th>
  {if !$showtags && !$editmeta}
   <th class="filesize">{str tag=Size section=artefact.file}</th>
   <th class="filedate">{str tag=Date section=artefact.file}</th>
  {/if}
  {if $showtags}
   <th class="filetags">{str tag=tags}</th>
  {/if}
  {if $editmeta}
   <th class="right"></th>
  {/if}
   <th class="right nowrap">
       <span class="accessible-hidden">{str tag=edit}</span>
   </th>
  </tr>
 </thead>
 <tbody>
  {foreach from=$filelist item=file}
    {if !$publishing || !$file->permissions || $file->can_republish}{assign var=publishable value=1}{else}{assign var=publishable value=0}{/if}
  <tr id="file:{$file->id}" class="{cycle values='r0,r1'} directory-item{if $file->isparent} parentfolder{/if}{if $file->artefacttype == 'folder'} folder{elseif $file->artefacttype == 'profileicon'} profileicon{/if}{if $highlight && $highlight == $file->id} highlight-file{/if}{if $edit == $file->id} hidden{/if}{if !$publishable && $file->artefacttype != 'folder'} disabled{/if}" {if !$publishable && $file->artefacttype != 'folder'} title="{str tag=notpublishable section=artefact.file}"{/if}>
    <td class="icon-container">
    {if !$file->isparent}
      {if $editable}
      <div class="icon-drag" id="drag:{$file->id}" tabindex="0">
        <img src="{$file->icon}" title="{str tag=clickanddragtomovefile section=artefact.file arg1=$file->title}" alt="{$file->title}">
      </div>
      {else}
        <img src="{$file->icon}">
      {/if}
    {/if}
    </td>
    <td class="filename">
    {assign var=displaytitle value=$file->title|safe}
    {if $file->artefacttype == 'folder'}
      <a href="{$querybase|safe}folder={$file->id}{if $owner}&owner={$owner}{if $ownerid}&ownerid={$ownerid}{/if}{/if}" id="changefolder:{$file->id}" class="changefolder" title="{str tag=gotofolder section=artefact.file arg1=$displaytitle}">
        <span class="accessible-hidden">{str tag=folder section=artefact.file}:</span>
        <span class="display-title {if $file->isparent}accessible-hidden{/if}">{$displaytitle}</span>
    </a>
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
    <td class="right s nowrap">
      {if $file->locked}
        <span class="dull">{str tag=Submitted section=view}</span>
      {elseif !$file->isparent}
        {if !isset($file->can_edit) || $file->can_edit !== 0}<input type="submit" class="btn-big-edit tag-edit submit" name="{$prefix}_edit[{$file->id}]" value="{str tag=edit}" title="{str tag=edit}" />{/if}
      {/if}
    </td>
    {/if}
    <!-- Ensure space for 3 buttons (in the case of a really long single line string in a user input field -->
    <td class="right s nowrap">
    {if $editable && !$file->isparent}
      {if $file->locked}
        <span class="dull">{str tag=Submitted section=view}</span>
      {elseif !isset($file->can_edit) || $file->can_edit != 0}
        {if $file->artefacttype == 'archive'}
        <a href="{$WWWROOT}artefact/file/extract.php?file={$file->id}">
            <img src="{theme_image_url filename="btn_extract"}" title="{str tag=Decompress section=artefact.file}" alt="{str(tag=decompressspecific section=artefact.file arg1=$displaytitle)|escape:html|safe}"/>
        </a>
        {/if}
        {if $file->artefacttype == 'folder'}
            {assign var=edittext value=str(tag=editfolderspecific section=artefact.file arg1=$displaytitle)}
            {assign var=deletetext value=str(tag=deletefolderspecific section=artefact.file arg1=$displaytitle)}
        {else}
            {assign var=edittext value=str(tag=editspecific arg1=$displaytitle)}
            {assign var=deletetext value=str(tag=deletespecific arg1=$displaytitle)}
        {/if}
        <input type="image" id="edit_{$file->id}" src="{theme_image_url filename="btn_edit"}" name="{$prefix}_edit[{$file->id}]" value="" title="{str tag=edit}" alt="{$edittext|escape:html|safe}" />
        <input type="image" id="delete_{$file->id}" src="{theme_image_url filename="btn_deleteremove"}" name="{$prefix}_delete[{$file->id}]" value="" title="{str tag=delete}" alt="{$deletetext|escape:html|safe}" />
      {/if}
    {/if}
    {if $selectable && ($file->artefacttype != 'folder' || $selectfolders) && $publishable && !$file->isparent}
      <input type="submit" class="select small" name="{$prefix}_select[{$file->id}]" id="{$prefix}_select_{$file->id}" value="{str tag=select}" title="{str tag=select}" />
    {/if}
    </td>
  </tr>
  {if $edit == $file->id}
    {include file="artefact:file:form/editfile.tpl" prefix=$prefix fileinfo=$file groupinfo=$groupinfo}
  {/if}
  {/foreach}
 </tbody>
</table>
<div id="downloadfolder">
  <a href="{$WWWROOT}artefact/file/downloadfolder.php?{$folderparams|safe}">{str tag=downloadfolderziplink section=artefact.file}</a>
</div>
{/if}
