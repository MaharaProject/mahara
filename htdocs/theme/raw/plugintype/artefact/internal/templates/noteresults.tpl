            {foreach from=$data item=n}
            <tr class="{cycle values='r1,r0'} {if $n->locked}warning{/if}">
                <td class="note-name">
                    {if $n->locked}
                    <h3>
                        <a class="notetitle" href="" id="n{$n->id}">
                            {$n->title}
                            <span class="accessible-hidden sr-only">
                            {str tag=clickformore}
                            </span>
                        </a>
                    </h3>
                    {else}
                    <h3>
                        <a class="notetitle" href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" id="n{$n->id}">
                            {$n->title}
                            <span class="accessible-hidden sr-only">
                            {str tag=clickformore}
                            </span>
                        </a>
                    </h3>
                    {/if}
                    <div id="n{$n->id}_desc" class="hidden">
                        <p>
                            {$n->description|clean_html|safe}
                        </p>
                        {if $n->files}
                        <div id="notefiles_{$n->id}" class="has-attachment">
                            <p>
                                <span class="icon left icon-paperclip" role="presentation" aria-hidden="true"></span>
                                <strong>
                                {str tag=attachedfiles section=artefact.blog}
                                </strong>
                            </p>
                            <ul class="list-group list-group-unbordered">
                            {foreach from=$n->files item=file}
                                <li class="list-group-item list-group-item-link small">
                                    <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}" {if $file->description} title="{$file->description}" data-toggle="tooltip"{/if}>
                                        {if $file->icon}
                                        <img src="{$file->icon}" alt="" class="file-icon">
                                        {else}
                                        <span class="icon icon-{$file->artefacttype} icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                                        {/if}
                                        <span>{$file->title|truncate:40} - ({$file->size|display_size})</span>
                                    </a>
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
                <td class="note-titled"><label class="hidden">{str tag=currenttitle section=artefact.internal}: </label>
                    {foreach from=$n->blocks item=b}
                    <div class="detail">
                        {$b.blocktitle}
                    </div>
                    {/foreach}
                </td>
                <td class="note-containedin"><label class="hidden">{str tag=containedin section=artefact.internal}: </label>
                    {foreach from=$n->views item=v}
                    <div class="detail">
                        <a href="{$v.fullurl}">{$v.viewtitle}</a>
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
                    <span class="dull text-muted">
                        {str tag=Submitted section=view}
                    </span>
                    {else}
                    <div class="btn-group">
                        <a href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" title="{str tag=edit}" class="btn btn-default btn-xs">
                            <span class="icon icon-lg icon-pencil" role="presentation" aria-hidden="true"></span>
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