<tr id="{$prefix}_edit_row"{if !$fileinfo} class="text-regular hidden editrow no-hover"{/if}>
    <td colspan="6" class="fileedittablewrap form-condensed">
        <div class="fileedittable">
            <h4 id="{$prefix}_edit_heading" class="edit-heading">
                {if $fileinfo}{if $fileinfo->artefacttype == 'folder'}{str tag=editfolder section=artefact.file}{else}{str tag=editfile section=artefact.file}{/if}{/if}
            </h4>
            <div class="required form-group">
                <label for="{$prefix}_edit_title">{str tag=name}<span class="requiredmarker"> *</span>
                </label>
                <input type="text" class="text" name="{$prefix}_edit_title" id="{$prefix}_edit_title" value="{$fileinfo->title}"/>
            </div>
            {if $fileinfo->artefacttype != 'profileicon'}
            <div class="form-group">
                <label for="{$prefix}_edit_description">{str tag=description}</label>
                <input type="text" class="text" name="{$prefix}_edit_description" id="{$prefix}_edit_description" value="{$fileinfo->description}" size="40" />
            </div>
            {/if}
            <div class="tags form-group">
                <label for="{$prefix}_edit_tags">{str tag=tags}</label>
                <input name="{$prefix}_edit_tags" id="{$prefix}_edit_tags" class="text" type="text" value="{foreach from=$fileinfo->tags item=tag name=tags}{if !$.foreach.tags.first}, {/if}{$tag}{/foreach}" />
                <span>{contextualhelp plugintype='artefact' pluginname='file' section='tags'}</span>
                <div class="description">{str tag=tagsdescprofile}</div>
            </div>
            {if $groupinfo}
            <div>
                <div>
                    <strong>{str tag=Permissions}</strong>
                </div>
                <div>
                    <div class="editpermissions">
                        <div>
                            <div>{str tag=Role section=group}</div>
                            {foreach from=$groupinfo.perm item=permname}
                            <div>{$permname}</div>
                            {/foreach}
                        </div>
                        {foreach from=$groupinfo.roles item=role key=r}
                        <div>
                            <div>{$role->display}</div>
                            {foreach from=$groupinfo.perm item=whocares key=permid}
                            {if $fileinfo}
                            <div class="checkbox form-group">
                                <label for="{$prefix}_permission_{$r}_{$permid}">{str tag=changerolepermissions section=group arg1=$permid arg2=$r}</label>
                                <input type="checkbox" class="permission checkbox" id="{$prefix}_permission_{$r}_{$permid}" name="{$prefix}_permission:{$r}:{$permid}"{if $fileinfo->permissions.$r.$permid} checked{/if}{if $r == 'admin'} disabled{/if} />
                            </div>
                            {else}
                            <div class="checkbox form-group">
                                <label for="{$prefix}_permission_{$r}_{$permid}">{str tag=changerolepermissions section=group arg1=$permid arg2=$r}</label>
                                <input type="checkbox" class="permission checkbox" id="{$prefix}_permission_{$r}_{$permid}" name="{$prefix}_permission:{$r}:{$permid}" {if $r == 'admin'} checked disabled{/if}/>
                            </div>
                            {/if}
                            {/foreach}
                        </div>
                        {/foreach}
                    </div>
                </div>
            </div>
          </div>
{/if}
        {license_form_files($prefix, 'edit')}
        <div class="form-group">
            <label for="{$prefix}_edit_allowcomments">
                {str tag=Comments section=artefact.comment}
            </label>
            <div class="form-switch">
                <div class="switch onoff" style="width: {$switchwidth}">
                    <input class="switchbox" type="checkbox" name="{$prefix}_edit_allowcomments" id="{$prefix}_edit_allowcomments" {if $fileinfo->allowcomments}checked {/if} aria-describedby />
                    <label class="switch-label" tabindex="1" for="{$prefix}_edit_allowcomments">
                        <span class="switch-inner" role="presentation"></span>
                        <span class="switch-indicator" role="presentation"></span>
                        <span class="state-label on" role="presentation" tabindex="-1">On</span>
                        <span class="state-label off" role="presentation" tabindex="-1">Off<span>
                    </label>
                    </div>
                </div>
            </div>
        <div>
        <div class="submitcancel form-group">
            <input type="submit" class="submit btn btn-primary" name="{$prefix}_update[{$fileinfo->id}]" id="{$prefix}_edit_artefact" value="{str tag=savechanges section=artefact.file}" />
            <input type="submit" class="cancel" name="{$prefix}_canceledit" id="{$prefix}_edit_cancel" value="{str tag=cancel}" />
        </div>
        <div>
            <div id="{$prefix}_edit_messages" class="ptm">
            </div>
        </div>
        </div>
    </td>
</tr>
