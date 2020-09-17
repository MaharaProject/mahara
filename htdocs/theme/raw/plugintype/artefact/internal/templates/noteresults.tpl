            {foreach from=$data item=n}
            <tr class="{cycle values='r1,r0'} {if $n->locked}warning{/if}">
                <td class="note-name">
                    {if $n->locked}
                    <h2>
                        <a class="notetitle" href="" id="n{$n->id}">
                            {$n->title}
                            <span class="accessible-hidden sr-only">
                            {str tag=clickformore}
                            </span>
                        </a>
                    </h2>
                    {else}
                    <h2>
                        <a class="notetitle" href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" id="n{$n->id}">
                            {$n->title}
                            <span class="accessible-hidden sr-only">
                            {str tag=clickformore}
                            </span>
                        </a>
                    </h2>
                    {/if}
                    <div id="n{$n->id}_desc" class="d-none">
                        <p>
                            {$n->description|clean_html|safe}
                        </p>
                        {if $n->files}
                        <div id="notefiles_{$n->id}" class="card has-attachment">
                            <div class="card-header">
                              <span class="icon left icon-paperclip icon-sm" role="presentation" aria-hidden="true"></span>
                              <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
                            </div>
                            <ul class="list-unstyled list-group">
                            {foreach from=$n->files item=file}
                                <li class="list-group-item">
                                    <a class="file-icon-link" href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}" {if $file->description} title="{$file->description}" data-toggle="tooltip"{/if}>
                                        {if $file->icon}
                                        <img src="{$file->icon}" alt="" class="file-icon">
                                        {else}
                                        <span class="icon icon-{$file->artefacttype} icon-lg text-default left file-icon" role="presentation" aria-hidden="true"></span>
                                        {/if}
                                    </a>
                                    <span class="title">
                                        <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}" {if $file->description} title="{$file->description}" data-toggle="tooltip"{/if}>
                                            <span class="text-small">{$file->title|truncate:40}</span>
                                        </a>
                                    </span>
                                    <span class="text-midtone text-small float-right">({$file->size|display_size})</span>
                                </li>
                            {/foreach}
                            </ul>
                        </div>
                        {/if}
                    </div>
                    {if $n->tags}
                    <div class="tags text-small">
                        <strong>{str tag=tags}</strong>: {list_tags tags=$n->tags owner=$n->owner}
                    </div>
                    {/if}
                </td>
                <td class="note-titled"><label class="d-none">{str tag=currenttitle section=artefact.internal}: </label>
                    {foreach from=$n->blocks item=b}
                    <div class="detail text-small">
                        {$b.blocktitle}
                    </div>
                    {/foreach}
                </td>
                <td class="note-containedin"><label class="d-none">{str tag=containedin section=artefact.internal}: </label>
                    {foreach from=$n->views item=v}
                    <div class="detail text-small">
                        <a href="{$v.fullurl}">{$v.viewtitle}</a>
                        {if $v.ownername} - {str tag=by section=view} {if $v.ownerurl}<a href="{$v.ownerurl}">{/if}{$v.ownername}{if $v.ownerurl}</a>{/if}{/if}
                    </div>
                    {if $v.extrablocks}
                    {for i 1 $v.extrablocks}
                    <div class="detail text-small">&nbsp;</div>
                    {/for}
                    {/if}
                    {/foreach}
                </td>
                <td class="note-attachment text-small">
                    <label class="d-none">
                        {str tag=Attachments section=artefact.resume}:
                    </label>
                    {$n->count}
                </td>
                <td class="control-buttons">
                    {if $n->locked}
                    <span class="dull text-muted">
                        {str tag=Submitted section=view}
                    </span>
                    {else}
                    <div class="btn-group">
                        <a href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" title="{str tag=edit}" class="btn btn-secondary btn-sm">
                            <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
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
