{if $config.select}
{include file="artefact:file:form/selectedlist.tpl" selectedlist=$selectedlist prefix=$prefix highlight=$highlight}
{/if}

<script type="text/javascript">
{$initjs}
</script>

<input type="hidden" name="folder" id="{$prefix}_folder" value="{$folder}" />
<input type="hidden" name="changefolder" id="{$prefix}_changefolder" value="" />
<input type="hidden" name="foldername" id="{$prefix}_foldername" value="{$foldername}" />

{if $config.select && !$browse}
<input type="submit" class="buttondk" id="{$prefix}_open_upload_browse" name="browse" value="{if $config.selectone}{str tag=selectafile section=artefact.file}{else}{str tag=addafile section=artefact.file}{/if}" />
{/if}

<div id="{$prefix}_upload_browse" class="upload_browse{if $config.select} select{if !$browse} hidden{/if}{/if}">

{if $config.select}
<input type="submit" class="buttondk" name="cancelbrowse" id="{$prefix}_close_upload_browse" value="{str tag=Close}" />
{/if}

<table class="fileupload">
 <tbody>
{if $config.upload}
  <tr><td><input type="hidden" name="uploadnumber" id="{$prefix}_uploadnumber" value="1" /></td></tr>
  <tr><td colspan=2 id="{$prefix}_upload_messages"></td></tr>
  {if $config.uploadagreement}
  <tr id="{$prefix}_agreement" class="uploadform">
    <th><label>{str tag='uploadfile' section='artefact.file'}</label></th>
    <td colspan=2>
      <input type="checkbox" name="notice" id="{$prefix}_notice" />
      {$agreementtext}
    </td>
  </tr>
  <tr class="uploadform">
    <th><label>{str tag='File' section='artefact.file'}</label></th>
    <td>
      <div id="{$prefix}_userfile_container"><input type="file" class="file" id="{$prefix}_userfile" name="userfile" size="40" /></div>
      <noscript><input type="submit" class="submit" name="upload" id="{$prefix}_upload" value="{str tag=upload section=artefact.file}" /></noscript>
      <script>setNodeAttribute('{$prefix}_userfile', 'disabled', true);</script>
    </td>
  </tr>
  <tr class="uploadform">
    <td colspan=2>
      <input type="button" class="button hidden" name="uploadcancel" id="{$prefix}_uploadcancel" value="{str tag=cancel}" />
    </td>
  </tr>
  {else}
  <tr>
    <th><label>{str tag='uploadfile' section='artefact.file'}</label></th>
    <td>
      <div id="{$prefix}_userfile_container"><input type="file" class="file" id="{$prefix}_userfile" name="userfile" size="40" /></div>
      <noscript><input type="submit" class="submit" name="upload" id="{$prefix}_upload" value="{str tag=upload section=artefact.file}" /></noscript>
    </td>
  </tr>
  {/if}
{/if}

{if $config.createfolder}
  <tr>
    <th><label>{str tag='createfolder' section='artefact.file'}</label></th>
    <td>
      <input type="text" class="text" name="createfolder_name" id="{$prefix}_createfolder_name" size="40" />
      <input type="submit" class="submit" name="createfolder" id="{$prefix}_createfolder" value="{str tag=createfolder section=artefact.file}" />
    </td>
  </tr>
  <tr>
    <td colspan=2 id="{$prefix}_createfolder_messages"></td>
  </tr>
{/if}
 </tbody>
</table>

{if $config.edit}
<input type="hidden" name="move" value="" />
<input type="hidden" name="moveto" value="" />
{/if}

<div id="{$prefix}_foldernav" class="foldernav">
{include file="artefact:file:form/folderpath.tpl" path=$path querybase=$querybase}
</div>

<div id="{$prefix}_filelist_container">
{include file="artefact:file:form/filelist.tpl" filelist=$filelist editable=$config.edit selectable=$config.select highlight=$highlight edit=$edit querybase=$querybase}
</div>

{* Edit form used when js is available *}
{if $edit <= 0}
<table class="hidden">
  <tbody id="{$prefix}_edit_placeholder">
  {include file="artefact:file:form/editfile.tpl"}
  </tbody>
</table>
{/if}

</div>