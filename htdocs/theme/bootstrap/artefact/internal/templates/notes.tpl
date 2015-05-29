{include file="header.tpl"}
<p class="lead">{str tag=notesdescription1 section=artefact.internal}</p>
<div class="table-responsive">
    <table id="notes" class="table">
        <thead>
            <tr>
                <th>{str tag=Note section=artefact.internal}</th>
                <th>{str tag=currenttitle section=artefact.internal}</th>
                <th>{str tag=containedin section=artefact.internal}</th>
                <th class="text-center">
                    <span class="fa fa-paperclip"></span>
                    <span class="sr-only">
                        {str tag=Attachments section=artefact.resume}
                    </span>
                </th>
                <th>
                    <span class="accessible-hidden sr-only">
                        {str tag=edit}
                    </span>
                </th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$data item=n}
            <tr class="{cycle values='r1,r0'}">
                <td class="note-name">
                    {if $n->locked}
                    <h3>
                        <a class="notetitle" href="" id="n{$n->id}">
                            {$n->title|str_shorten_text:80:true}
                            <span class="accessible-hidden sr-only">
                            {str tag=clickformore}
                            </span>
                        </a>
                    </h3>
                    {else}
                    <h3>
                        <a class="notetitle" href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" id="n{$n->id}">
                            {$n->title|str_shorten_text:80:true}
                            <span class="accessible-hidden sr-only">
                            {str tag=clickformore}
                            </span>
                        </a>
                    </h3>
                    {/if}
                    <div id="n{$n->id}_desc" class="hidden">
                        <p class="mtm">
                            {$n->description|clean_html|safe}
                        </p>
                        {if $n->files}
                        <div id="notefiles_{$n->id}">
                            <p>
                                <span class="fa fa-lg prs fa-paperclip"></span>
                                <strong>
                                {str tag=attachedfiles section=artefact.blog}
                                </strong>
                            </p>
                            <ul class="list-group list-unstyled mbs">
                            {foreach from=$n->files item=file}
                                <li class="list-group-item-text list-group-item-link">
                                    <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}" {if $file->description} title="{$file->description}" data-toggle="tooltip"{/if}>
                                        <div class="file-icon">
                                            {if $file->icon}
                                            <img src="{$file->icon}" alt="">
                                            {else}
                                            <span class="fa fa-{$file->artefacttype} fa-lg text-default"></span>
                                            {/if}
                                        </div>
                                        <span>{$file->title|truncate:40} - ({$file->size|display_size})</span>
                                    </a>
                                </li>
                            {/foreach}
                            </ul>
                        </div>
                        {/if}
                    </div>
                    {if $n->tags}
                    <div class="tags text-small ptm">{str tag=tags}</span>: {list_tags tags=$n->tags owner=$n->owner}</div>
                    {/if}
                </td>
                <td class="note-titled"><label class="hidden">{str tag=currenttitle section=artefact.internal}: </label>
                    {foreach from=$n->blocks item=b}
                    <div class="detail">
                        {$b.blocktitle|str_shorten_text:30:true}
                    </div>
                    {/foreach}
                </td>
                <td class="note-containedin"><label class="hidden">{str tag=containedin section=artefact.internal}: </label>
                    {foreach from=$n->views item=v}
                    <div class="detail">
                        <a href="{$v.fullurl}">{$v.viewtitle|str_shorten_text:30:true}</a>
                        {if $v.ownername} - {str tag=by section=view} {if $v.ownerurl}<a href="{$v.ownerurl}">{/if}{$v.ownername}{if $v.ownerurl}</a>{/if}{/if}
                    </div>
                    {if $v.extrablocks}
                    {for i 1 $v.extrablocks}
                    <div class="detail">&nbsp;</div>
                    {/for}
                    {/if}
                    {/foreach}
                </td>
                <td class="note-attachment">
                    <label class="hidden">
                        {str tag=Attachments section=artefact.resume}: 
                    </label> 
                    {$n->count}
                </td>
                <td class="control-buttons">
                    {if $n->locked}
                    <span class="s dull text-muted">
                        {str tag=Submitted section=view}
                    </span>
                    {else}
                    <div class="btn-group">
                        <a href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" title="{str tag=edit}" class="btn btn-default btn-xs">
                            <span class="fa fa-pencil"></span>
                            <span class="sr-only">
                                {str(tag=editspecific arg1=$n->title)|escape:html|safe}
                            </span>
                        </a>
                        {if $n->deleteform}{$n->deleteform|safe}{/if}
                    </div>
                    {/if}
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
{$pagination.html|safe}
{include file="footer.tpl"}
