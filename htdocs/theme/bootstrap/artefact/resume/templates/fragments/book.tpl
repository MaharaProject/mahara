<div class="panel panel-default">
    {if !$hidetitle}
    <h3 class="resumeh3 panel-heading">
        {str tag='book' section='artefact.resume'}
        {if $controls}
        {contextualhelp plugintype='artefact' pluginname='resume' section='addbook'}
        {/if}
    </h3>{/if}
    <div class="panel-body table-responsive">
        <table id="booklist{$suffix}" class="tablerenderer resumefour resumecomposite fullwidth table">
            <thead>
                <tr>
                    {if $controls}<th class="resumecontrols">
                        <span class="accessible-hidden sr-only">{str tag=move}</span>
                    </th>{/if}
                    <th>{str tag='title' section='artefact.resume'}</th>
                    <th class="resumeattachments text-center">
                        <span class="fa fa-paperclip"></span>
                        <span class="sr-only">{str tag=Attachments section=artefact.resume}</span>
                    </th>
                    {if $controls}<th class="resumecontrols">
                        <span class="accessible-hidden sr-only">{str tag=edit}</span>
                    </th>{/if}
                </tr>
            </thead>
            <!-- Table body is rendered by JavaScript in artefact/resume/lib.php -->
            <!-- <tbody>
                {foreach from=$rows item=row}
                <tr class="{cycle values='r0,r1'}">
                    {if $controls}<td class="buttonscell"></td>{/if}
                    <td>
                        <div class="expandable-head">
                            {if $row->description || $row->attachments || $row->url}<a class="toggle textonly" href="#">{else}<strong>{/if}
                                {$row->title}
                            {if $row->description || $row->attachments || $row->url}</a>{else}</strong>{/if}
                            <div>{$row->date}</div>
                        </div>
                        <div class="expandable-body">
                            <div class="compositedesc">
                                {$row->description}
                                {if $row->url}<p><a href="{$row->url}" target="_blank">{$row->url}</a></p>{/if}
                            </div>
                            {if $row->attachments}
                            <table class="cb attachments fullwidth">
                                <thead class="expandable-head">
                                    <tr>
                                        <th colspan="2"><a class="toggle" href="#">{str tag='attachedfiles' section='artefact.blog'}</a></th>
                                    </tr>
                                </thead>
                                <tbody class="expandable-body">
                                    {foreach from=$row->attachments item=item}
                                    <tr>
                                        {if $icons}<td class="iconcell"><img src="{$item->iconpath}" alt=""></td>{/if}
                                        <td><a href="{$item->viewpath}">{$item->title}</a> ({$item->size}) - <strong><a href="{$item->downloadpath}">{str tag=Download section=artefact.file}</a></strong>
                                        <br>{$item->description}</td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                            {/if}
                        </div>
                    </td>
                    <td class="center">{$row->clipcount}</td>
                    {if $controls}<td class="buttonscell"></td>{/if}
                </tr>
                {/foreach}
            </tbody> -->
        </table>
    </div>
    {if $controls}
    <div class="panel-footer has-form">
        <div id="bookform" class="js-expanded-form collapse mtl mlm" data-action='reset-on-collapse'>
            {$compositeforms.book|safe}
        </div>
        <button id="addbookbutton" data-toggle="collapse" data-target="#bookform" aria-expanded="false" aria-controls="bookform" class="pull-right btn btn-default btn-sm collapsed expand-add-button">
            <span class="show-form">
                {str tag='add'}
                <span class="fa fa-chevron-down pls"></span>
            </span>
            <span class="hide-form">
                {str tag='cancel'}
                <span class="fa fa-chevron-up pls"></span>
            </span>
        </button>
        
        {if $license}
        <div class="resumelicense">
        {$license|safe}
        </div>
        {/if}
    </div>
    {/if}
</div>
<!--  <script type="text/javascript">
setupExpanders(jQuery('#booklist{$suffix}'));
</script> -->
