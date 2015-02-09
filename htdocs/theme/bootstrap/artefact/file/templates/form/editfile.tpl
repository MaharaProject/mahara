  <tr id="{$prefix}_edit_row"{if !$fileinfo} class="hidden editrow"{/if}>
    <td></td>
    <td colspan="5" class="fileedittablewrap">
      <div class="fileedittable">
          <div>
            <div id="{$prefix}_edit_heading" class="edit-heading">
            {if $fileinfo}{if $fileinfo->artefacttype == 'folder'}{str tag=editfolder section=artefact.file}{else}{str tag=editfile section=artefact.file}{/if}{/if}
            </div>
          </div>
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
{/if}
          {license_form_files($prefix, 'edit')}
          <div class="checkbox form-group">
              <label for="{$prefix}_edit_allowcomments">{str tag=allowcomments section=artefact.comment}</label>
              <input type="checkbox" class="checkbox" name="{$prefix}_edit_allowcomments" id="{$prefix}_edit_allowcomments" {if $fileinfo->allowcomments}checked {/if}/>
          </div>
          <div>
            <div>
              <input type="submit" class="submit btn btn-success" name="{$prefix}_update[{$fileinfo->id}]" id="{$prefix}_edit_artefact" value="{str tag=savechanges section=artefact.file}" />
              <input type="submit" class="cancel btn btn-danger" name="{$prefix}_canceledit" id="{$prefix}_edit_cancel" value="{str tag=cancel}" />
            </div>
          </div>
          <div>
            <div id="{$prefix}_edit_messages" class="ptm">
            </div>
          </div>
      </div>
    </td>
  </tr>
