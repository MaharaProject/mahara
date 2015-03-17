<p id="{$prefix}_empty_selectlist"{if $selectedlist} class="hidden"{/if}>{if !$selectfolders}{str tag=nofilesfound section=artefact.file}{/if}</p>
<table id="{$prefix}_selectlist"  class="fullwidth{if !$selectedlist} hidden{/if}">
 <thead>
  <tr>
   <th></th>
   <th>{str tag=Name section=artefact.file}</th>
   <th>{str tag=Description section=artefact.file}</th>
   <th></th>
  </tr>
 </thead>
 <tbody>
  {foreach from=$selectedlist item=file}
    {assign var=displaytitle value=$file->title|str_shorten_text:34|safe}
  <tr class="{cycle values='r0,r1'}{if $highlight && $highlight == $file->id} highlight-file{/if}">
    <td class="icon-container">
      <img src="{if $file->artefacttype == 'image' || $file->artefacttype == 'profileicon'}{$WWWROOT}artefact/file/download.php?file={$file->id}&size=24x24{else}{theme_image_url filename=`$file->artefacttype`}{/if}">
    </td>
    <td class="filename">
      {if $selectfolders}{$displaytitle}{else}<a href="{$WWWROOT}artefact/file/download.php?file={$file->id}" target="_blank" title="{str tag=downloadfile section=artefact.file arg1=$displaytitle}">{$displaytitle}</a>{/if}
    </td>
    <td class="filedescription">{$file->description}</td>
    <td class="right s">
       <input type="submit" class="button submit unselect" name="{$prefix}_unselect[{$file->id}]" value="{str tag=remove}" id="{$prefix}_unselect_{$file->id}" />
       <input type="hidden" class="hidden" id="{$prefix}_selected[{$file->id}]" name="{$prefix}_selected[{$file->id}]" value="{$file->id}">
    </td>
  </tr>
  {/foreach}
 </tbody>
</table>
