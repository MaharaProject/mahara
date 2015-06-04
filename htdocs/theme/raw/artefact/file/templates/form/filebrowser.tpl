{if $config.select}
{include file="artefact:file:form/selectedlist.tpl" selectedlist=$selectedlist prefix=$prefix highlight=$highlight selectfolders=$config.selectfolders}
{/if}

<script type="text/javascript">
{$initjs|safe}
</script>

<input type="hidden" name="folder" id="{$prefix}_folder" value="{$folder}" />
<input type="hidden" name="{$prefix}_changefolder" id="{$prefix}_changefolder" value="" />
<input type="hidden" name="{$prefix}_foldername" id="{$prefix}_foldername" value="{$foldername}" />

{if $config.select}
<div id="{$prefix}_open_upload_browse_container">

	{if $config.selectmodal}
		<div id="{$prefix}_upload_browse" class="filebrowser in-collapsible">
	{else}
		<button type="button" class="btn btn-default" data-toggle="modal" data-target="#{$prefix}_upload_browse">
			<span class="fa fa-paperclip fa-lg prs"></span>
			{str tag=addafile section=artefact.file}
		</button>
		<div id="{$prefix}_upload_browse" class="modal fade js-filebrowser" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
	{/if}

{else}
	<div id="{$prefix}_upload_browse" class="upload_browse select">
{/if}

		{if $config.select && !$config.alwaysopen}
			<button type="button" class="close" data-dismiss="modal">
				<span class="sr-only">{str tag=Close}</span>
				<span aria-hidden="true">&times;</span>
			</button>
		{/if}

		{if $tabs}
			<input type="hidden" name="{$prefix}_owner" id="{$prefix}_owner" value="{$tabs.owner}" />
			<input type="hidden" name="{$prefix}_ownerid" id="{$prefix}_ownerid" value="{$tabs.ownerid}" />
			<input type="hidden" name="{$prefix}_changeowner" id="{$prefix}_changeowner" value="" />

			<div id="{$prefix}_ownertabs">
				{include file="artefact:file:form/ownertabs.tpl" tabs=$tabs prefix=$prefix querybase=$querybase}
			</div>

			<div id="artefactchooser-body">
				<div id="{$prefix}_ownersubtabs" {if !$tabs.subtabs}class="hidden"{/if}>
				{if $tabs.subtabs}
					{include file="artefact:file:form/ownersubtabs.tpl" tabs=$tabs prefix=$prefix querybase=$querybase}
				{/if}
				</div>
		{/if}

		{if $config.upload}
		<div id="{$prefix}_upload_container" class="{if $config.selectone || $config.selectmodal} panel-fake{else} panel panel-default fileupload {/if} {if ($tabs && !$tabs.upload) || $uploaddisabled} hidden{/if}">
			{* config.uploadagreement: disable the file chooser unless the agreement is checked *}
			{* config.simpleupload: the form only contains a file chooser *}
			{* config.submitbutton: add submit button even if js is enabled & don't start uploading as soon as a file is chosen *}

			<input type="hidden" name="{$prefix}_uploadnumber" id="{$prefix}_uploadnumber" value="1" />
			<input type="hidden" name="MAX_FILE_SIZE" value="{$phpmaxfilesize}" />
			<div id="{$prefix}_upload_messages"></div>

			{if $config.uploadagreement}
				<div id="{$prefix}_agreement" class="uploadform clearfix">
					<label for="{$prefix}_notice">{str tag='uploadfile' section='artefact.file'}</label>
					<input type="checkbox" name="{$prefix}_notice" id="{$prefix}_notice" />
					{$agreementtext|clean_html|safe}
				</div>
			{/if}

			<div class="uploadform userfile clearfix ptl">
				<label for="{$prefix}_userfile">
					{if $config.simpleupload}
						{str tag='uploadfile' section='artefact.file'}
					{else}
						{str tag='File' section='artefact.file'}
					{/if}
				</label>
				<span id="{$prefix}_userfile_container"><input type="file" class="file"  {$accepts|safe} id="{$prefix}_userfile" name="userfile[]" multiple size="20" /></span>
				<span id="{$prefix}_userfile_maxuploadsize" class="description">({str tag=maxuploadsize section=artefact.file} {$maxuploadsize})</span>

				{if $config.uploadagreement}
					<script>setNodeAttribute('{$prefix}_userfile', 'disabled', true);</script>
				{/if}
			</div>

			{if $config.resizeonuploaduseroption}
			<div id="{$prefix}_resizeonuploaduseroption" class="description">
				{str tag='resizeonuploadenablefilebrowser1' section='artefact.file' arg1=$resizeonuploadmaxwidth arg2=$resizeonuploadmaxheight}

				<input type="checkbox" name="{$prefix}_resizeonuploaduserenable" id="{$prefix}_resizeonuploaduserenable" {if $resizeonuploadenable && $config.resizeonuploaduserdefault}checked{/if} />
				{contextualhelp plugintype='artefact' pluginname='file' form='files_filebrowser' element='resizeonuploaduseroption'}
			</div>
			{/if}

			<div id="file_dropzone_container" class="{$prefix}">
				<div id="fileDropzone" class="dropzone-previews" style="display:none;">
					<div class="dz-message">{str tag=dragdrophere section=artefact.file}</div>
				</div>
			</div>

			<div class="uploadform clearfix pbl">
				<div id="{$prefix}_uploadsubmit_container">
					{* filebrowser.js may add a submit button in here even if config.submitbutton is off *}

					{if $config.submitbutton}
					<input type="submit" class="submit nojs-hidden-block" name="{$prefix}_uploadsubmit" id="{$prefix}_uploadsubmit" value="{str tag=upload section=artefact.file}" />
					{/if}

					<noscript><input class="submit btn btn-success" type="submit" name="{$prefix}_upload" id="{$prefix}_upload" value="{str tag=upload section=artefact.file}" /></noscript>
				</div>
			</div>

			{$licenseform|safe}
		</div>
		{/if}


		{if $config.upload}
		<div id="{$prefix}_upload_disabled" class="uploaddisabled{if !$uploaddisabled} hidden{/if}">
			{str tag="cannoteditfolder" section=artefact.file}
		</div>
		{/if}


		{if $config.edit}
			<input type="hidden" name="{$prefix}_move" id="{$prefix}_move" value="" />
			<input type="hidden" name="{$prefix}_moveto" id="{$prefix}_moveto" value="" />
		{/if}


		{if $config.createfolder}
			<div id="createfolder" class="{if $uploaddisabled}hidden{/if} form-createfolder form-group text-right ptxl">
				<div id="{$prefix}_createfolder_messages"></div>
				<label for="{$prefix}_createfolder_name" class="accessible-hidden sr-only">
					{str tag=createfolder section=artefact.file}
				</label>
				<input type="text" class="text" name="{$prefix}_createfolder_name" id="{$prefix}_createfolder_name" size="40" />
				<button type="submit" class="btn btn-primary" name="{$prefix}_createfolder" id="{$prefix}_createfolder" value="{str tag=createfolder section=artefact.file}">
					<span class="fa fa-folder-open"></span>
					{str tag=createfolder section=artefact.file}
				</button>
			</div>
		{/if}

		<div class="filelist-wrapper panel panel-secondary">
			<h3 id="{$prefix}_foldernav" class="panel-heading ptm pbm mtl mbl pll ">
			{include file="artefact:file:form/folderpath.tpl" path=$path querybase=$querybase owner=$tabs.owner ownerid=$tabs.ownerid}
			</h3>

			<div id="{$prefix}_filelist_container">
				{include file="artefact:file:form/filelist.tpl" prefix=$prefix filelist=$filelist folderdownload=$folderdownload folderparams=$folderparams editable=$config.edit selectable=$config.select highlight=$highlight edit=$edit querybase=$querybase groupinfo=$groupinfo owner=$tabs.owner ownerid=$tabs.ownerid selectfolders=$config.selectfolders showtags=$config.showtags editmeta=$config.editmeta}
			</div>
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
{if $config.select}
</div>
{/if}
