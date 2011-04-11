{if $config.select}
{include file="artefact:file:form/selectedlist.tpl" selectedlist=$selectedlist prefix=$prefix highlight=$highlight}
{/if}

<script type="text/javascript">
{$initjs|safe}
</script>

<input type="hidden" name="folder" id="{$prefix}_folder" value="{$folder}" />
<input type="hidden" name="{$prefix}_changefolder" id="{$prefix}_changefolder" value="" />
<input type="hidden" name="{$prefix}_foldername" id="{$prefix}_foldername" value="{$foldername}" />

{if $config.select && !$browse}
<div id="{$prefix}_open_upload_browse_container">
<input type="submit" class="buttondk" id="{$prefix}_open_upload_browse" name="browse" value="{if $config.selectone}{str tag=selectafile section=artefact.file}{else}{str tag=addafile section=artefact.file}{/if}" />{if $config.browsehelp}{contextualhelp plugintype=$config.plugintype pluginname=$config.pluginname section=$config.browsehelp}{/if}
</div>
{/if}

<div id="{$prefix}_upload_browse" class="upload_browse{if $config.select} select{if !$browse} hidden{/if}{/if}">

{if $config.select && !$config.alwaysopen}
<input type="submit" class="buttondk" name="{$prefix}_cancelbrowse" id="{$prefix}_close_upload_browse" value="{str tag=Close}" />
{/if}

{if $tabs}
<input type="hidden" name="owner" id="{$prefix}_owner" value="{$tabs.owner}" />
<input type="hidden" name="ownerid" id="{$prefix}_ownerid" value="{$tabs.ownerid}" />
<input type="hidden" name="{$prefix}_changeowner" id="{$prefix}_changeowner" value="" />
<div id="{$prefix}_ownertabs">
{include file="artefact:file:form/ownertabs.tpl" tabs=$tabs prefix=$prefix querybase=$querybase}
</div>
<div id="artefactchooser-body">
  <div id="{$prefix}_ownersubtabs">
  {if $tabs.subtabs}{include file="artefact:file:form/ownersubtabs.tpl" tabs=$tabs prefix=$prefix querybase=$querybase}{/if}
  </div>
{/if}

<table id="{$prefix}_upload_container" class="fileupload{if ($tabs && !$tabs.upload) || $uploaddisabled} hidden{/if}">
 <tbody>
{if $config.upload}
  {* config.uploadagreement: disable the file chooser unless the agreement is checked *}
  {* config.simpleupload: the form only contains a file chooser *}
  {* config.submitbutton: add submit button even if js is enabled & don't start uploading as soon as a file is chosen *}
  <input type="hidden" name="{$prefix}_uploadnumber" id="{$prefix}_uploadnumber" value="1" />
  <input type="hidden" name="MAX_FILE_SIZE" value="{$phpmaxfilesize}" />
  <tr><td colspan=2 id="{$prefix}_upload_messages"></td></tr>
  {if $config.uploadagreement}
  <tr id="{$prefix}_agreement" class="uploadform">
    <th><label>{str tag='uploadfile' section='artefact.file'}</label></th>
    <td>
      <input type="checkbox" name="{$prefix}_notice" id="{$prefix}_notice" />
      {$agreementtext|clean_html|safe}
    </td>
  </tr>
  {/if}
  <tr class="uploadform">
    <th><label>{if $config.simpleupload}{str tag='uploadfile' section='artefact.file'}{else}{str tag='File' section='artefact.file'}{/if}</label></th>
    <td>
      <span id="{$prefix}_userfile_container"><input type="file" class="file" id="{$prefix}_userfile" name="userfile" size="40" /></span>
      <span id="{$prefix}_userfile_maxuploadsize">({str tag=maxuploadsize section=artefact.file} {$maxuploadsize})</span>
      {if $config.uploadagreement}<script>setNodeAttribute('{$prefix}_userfile', 'disabled', true);</script>{/if}
    </td>
  </tr>
  <tr class="uploadform">
    <th></th>
    <td id="{$prefix}_uploadsubmit_container">{* filebrowser.js may add a submit button in here even if config.submitbutton is off *}
      {if $config.submitbutton}
      <input type="submit" class="submit nojs-hidden-block" name="{$prefix}_uploadsubmit" id="{$prefix}_uploadsubmit" value="{str tag=upload section=artefact.file}" />
      {/if}
      <noscript><input type="submit" class="submit" name="{$prefix}_upload" id="{$prefix}_upload" value="{str tag=upload section=artefact.file}" /></noscript>
    </td>
  </tr>
{/if}
 </tbody>
</table>
{if $config.upload}
<div id="{$prefix}_upload_disabled" class="uploaddisabled{if !$uploaddisabled} hidden{/if}">{str tag="cannoteditfolder" section=artefact.file}</div>
{/if}

{if $config.edit}
<input type="hidden" name="{$prefix}_move" id="{$prefix}_move" value="" />
<input type="hidden" name="{$prefix}_moveto" id="{$prefix}_moveto" value="" />
{/if}
  
<div id="{$prefix}_foldernav" class="foldernav">
{include file="artefact:file:form/folderpath.tpl" path=$path querybase=$querybase owner=$tabs.owner ownerid=$tabs.ownerid}
</div>

{if $config.createfolder}
  <div id="createfolder"{if $uploaddisabled} class="hidden"{/if}><div id="{$prefix}_createfolder_messages" class="createfolder-message"></div>
    <input type="text" class="text" name="{$prefix}_createfolder_name" id="{$prefix}_createfolder_name" size="40" />
    <input type="submit" class="submit" name="{$prefix}_createfolder" id="{$prefix}_createfolder" value="{str tag=createfolder section=artefact.file}" /></div>
{/if}

<div id="{$prefix}_filelist_container">
{include file="artefact:file:form/filelist.tpl" prefix=$prefix filelist=$filelist editable=$config.edit selectable=$config.select highlight=$highlight edit=$edit querybase=$querybase groupinfo=$groupinfo owner=$tabs.owner ownerid=$tabs.ownerid selectfolders=$config.selectfolders showtags=$config.showtags editmeta=$config.editmeta}
</div>

{* Edit form used when js is available *}
{if $edit <= 0}
<table class="hidden">
  <tbody id="{$prefix}_edit_placeholder">
  {include file="artefact:file:form/editfile.tpl" prefix=$prefix groupinfo=$groupinfo}
  </tbody>
</table>
{/if}

{if $tabs}
</div>
{/if}

</div>
