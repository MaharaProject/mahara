{if $config.select}
{include file="artefact:file:form/selectedlist.tpl" selectedlist=$selectedlist prefix=$prefix highlight=$highlight selectfolders=$config.selectfolders}
{/if}

<script type="application/javascript">
{$initjs|safe}
</script>

<input type="hidden" name="folder" id="{$prefix}_folder" value="{$folder}" />
<input type="hidden" name="{$prefix}_changefolder" id="{$prefix}_changefolder" value="" />
<input type="hidden" name="{$prefix}_foldername" id="{$prefix}_foldername" value="{$foldername}" />

{if $config.select}
<div id="{$prefix}_open_upload_browse_container" class="form-condensed">

    {if $config.selectmodal}
        <div id="{$prefix}_upload_browse" class="filebrowser in-collapsible">
    {else}
        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#{$prefix}_upload_browse">
            <span class="icon icon-paperclip icon-lg left" role="presentation" aria-hidden="true"></span>
            {str tag=addafile section=artefact.file}
        </button>
        <div id="{$prefix}_upload_browse" class="modal fade js-filebrowser" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    {/if}

{else}
    <div id="{$prefix}_upload_browse" class="form-condensed upload_browse select">
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
        <div id="{$prefix}_upload_container" class="clearfix {if $config.selectone || $config.selectmodal} panel-fake{else} panel panel-default fileupload {/if} {if ($tabs && !$tabs.upload) || $uploaddisabled} hidden{/if}">
            {* config.uploadagreement: disable the file chooser unless the agreement is checked *}
            {* config.simpleupload: the form only contains a file chooser *}
            {* config.submitbutton: add submit button even if js is enabled & don't start uploading as soon as a file is chosen *}

            {* config.uploadagreement: disable the file chooser unless the agreement is checked *}
            {* config.simpleupload: the form only contains a file chooser *}
            {* config.submitbutton: add submit button even if js is enabled & don't start uploading as soon as a file is chosen *}

            <input type="hidden" name="{$prefix}_uploadnumber" id="{$prefix}_uploadnumber" value="1"/>
            <input type="hidden" name="MAX_FILE_SIZE" value="{$phpmaxfilesize}" />
            <div id="{$prefix}_upload_messages"></div>
            <h3 class="title">{str tag='uploadfile' section='artefact.file'}</h3>

            <div class="row">
                {if $config.uploadagreement || $licenseform}
                <div class="fileupload-container col-md-6">
                    {if $config.uploadagreement}
                    <div id="{$prefix}_agreement" class="uploadform">
                        <label for="{$prefix}_notice">
                            <input type="checkbox" name="{$prefix}_notice" id="{$prefix}_notice" />
                            {$agreementtext|clean_html|safe}
                        </label>
                    </div>
                    {/if}
                    <div class="fileuploadlicense">
                        {$licenseform|safe}
                    </div>
                </div>
                {/if}

                <div class="fileupload-container {if $config.uploadagreement || $licenseform}col-md-6{else}col-md-12{/if}">
                    {if $config.resizeonuploaduseroption}
                    <p id="{$prefix}_resizeonuploaduseroption" class="resize-image">
                        <label>
                            <input type="checkbox" name="{$prefix}_resizeonuploaduserenable" id="{$prefix}_resizeonuploaduserenable" {if $resizeonuploadenable && $config.resizeonuploaduserdefault}checked{/if} />
                            {str tag='resizeonuploadenablefilebrowser1' section='artefact.file' arg1=$resizeonuploadmaxwidth arg2=$resizeonuploadmaxheight}
                        </label>
                        {contextualhelp plugintype='artefact' pluginname='file' form='files_filebrowser' element='resizeonuploaduseroption'}
                    </p>
                    {/if}

                    <div class="uploadform userfile">
                        <label class="lead" for="{$prefix}_userfile">
                            {str tag='File' section='artefact.file'}
                        </label>

                        <span id="{$prefix}_userfile_container">
                            <input type="file" class="file"  {$accepts|safe} id="{$prefix}_userfile" name="userfile[]" multiple size="20" />
                        </span>

                        <span id="{$prefix}_userfile_maxuploadsize" class="file-description">
                            ({str tag=maxuploadsize section=artefact.file} {$maxuploadsize})
                        </span>

                        {if $config.uploadagreement}
                            <script>setNodeAttribute('{$prefix}_userfile', 'disabled', true);</script>
                        {/if}
                    </div>

                    <div id="file_dropzone_container" class="{$prefix}">
                        <div id="fileDropzone" class="dropzone-previews" style="display:none;">
                            <div class="dz-message">{str tag=dragdrophere section=artefact.file}</div>
                        </div>
                    </div>

                    <div class="uploadform">
                        <div id="{$prefix}_uploadsubmit_container">
                            {* filebrowser.js may add a submit button in here even if config.submitbutton is off *}

                            {if $config.submitbutton}
                                <input type="submit" class="submit nojs-hidden-block" name="{$prefix}_uploadsubmit" id="{$prefix}_uploadsubmit" value="{str tag=upload section=artefact.file}" />
                            {/if}

                            <noscript><input class="submit btn btn-primary" type="submit" name="{$prefix}_upload" id="{$prefix}_upload" value="{str tag=upload section=artefact.file}" /></noscript>
                        </div>
                    </div>
                </div>
            </div>
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
            <div id="createfolder" class="{if $uploaddisabled}hidden{/if} form-group">
                <div id="{$prefix}_createfolder_messages"></div>
                <label for="{$prefix}_createfolder_name" class="accessible-hidden sr-only">
                    {str tag=createfolder section=artefact.file}
                </label>
                <span class="input-group">
                    <input type="text" class="text form-control" name="{$prefix}_createfolder_name" id="{$prefix}_createfolder_name" size="40" />
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary" name="{$prefix}_createfolder" id="{$prefix}_createfolder" value="{str tag=createfolder section=artefact.file}">
                            <span class="icon icon-folder-open" role="presentation" aria-hidden="true"></span>
                            {str tag=createfolder section=artefact.file}
                        </button>
                    </span>
                </span>
            </div>
        {/if}

        <div class="filelist-wrapper panel panel-secondary">
            <h3 id="{$prefix}_foldernav" class="panel-heading">
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
        {include file="pagemodal.tpl"}
    {if $tabs}
    </div>
    {/if}
</div>
{if $config.select}
</div>
{/if}
