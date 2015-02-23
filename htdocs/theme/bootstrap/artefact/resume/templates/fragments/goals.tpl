<fieldset>{if !$hidetitle}<legend class="resumeh3">{str tag='mygoals' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='mygoals'}
{/if}
</legend>{/if}
<div class="table-responsive">
<table id="goalslist{$suffix}" class="tablerenderer fullwidth table table-striped">
    <thead>
        <tr>
            <th>{str tag='goals' section='artefact.resume'}</th>
            <th class="resumeattachments text-center">
              <span class="fa fa-paperclip">
              <span class="sr-only">{str tag=Attachments section=artefact.resume}</span>
            </th>
            <th><span class="accessible-hidden sr-only">{str tag=edit}</span></th>
        </tr>
    </thead>
    <tbody>
    {foreach from=$goals item=n}
    <tr class="{cycle values='r0,r1'}">
        <td class="goaltitle">
        {if $n->exists}
          <h3><a class="goaltitle" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?id={$n->id}" id="n{$n->id}">{str tag=$n->artefacttype section='artefact.resume'}</a></h3>
          <div id="n{$n->id}_desc" class="hidden detail">{if $n->description != ''}{$n->description|clean_html|safe}{else}{str tag=nodescription section=artefact.resume}{/if}
          {if $n->files}
              <div id="resume_{$n->id}">
                  <table class="attachments fullwidth">
                      <col width="5%">
                      <col width="40%">
                      <col width="55%">
                      <tbody>
                          <tr><th colspan=3>{str tag=attachedfiles section=artefact.blog}</th></tr>
                          {foreach from=$n->files item=file}
                              <tr class="{cycle values='r1,r0'}">
                                  <td><img src="{$file->icon}" alt=""></td>
                                  <td class="valign"><a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}">{$file->title}</a></td>
                                  <td class="valign">{$file->description}</td>
                              </tr>
                          {/foreach}
                      </tbody>
                  </table>
              </div>
          </div>
          {/if}
        {else}
          <h3>{str tag=$n->artefacttype section='artefact.resume'}</h3>
        {/if}
        </td>
        <td align="center">{$n->count}</td>
        <td class="right buttonscell control-buttons">
        {if $n->exists}
        <a id="goals_edit_{$n->artefacttype}" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?id={$n->id}" title="{str tag=edit$n->artefacttype section=artefact.resume}" class="btn btn-default btn-xs">
            <span class="fa fa-pencil"></span>
            <span class="sr-only">{str tag=edit}</span>
        </a>
        {else}
        <a id="goals_edit_{$n->artefacttype}" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?type={$n->artefacttype}" title="{str tag=edit$n->artefacttype section=artefact.resume}" class="btn btn-default btn-xs">
          <span class="fa fa-pencil"></span>
          <span class="sr-only">{str tag=edit}</span>
        </a>
        {/if}
        </td>
    </tr>
    {/foreach}
    </tbody>
</table>
</div>
{if $license}
<div class="resumelicense">
{$license|safe}
</div>
{/if}
</fieldset>
