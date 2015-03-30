<fieldset>{if !$hidetitle}<legend class="resumeh3">{str tag='mygoals' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='mygoals'}
{/if}
</legend>{/if}
<table id="goalslist{$suffix}" class="tablerenderer fullwidth">
    <thead>
        <tr>
            <th>{str tag='goals' section='artefact.resume'}</th>
            <th class="resumeattachments center"><img src="{theme_image_url filename="attachment"}" title="{str tag=Attachments section=artefact.resume}" alt="{str tag=Attachments section=artefact.resume}" /></th>
            <th><span class="accessible-hidden">{str tag=edit}</span></th>
        </tr>
    </thead>
    <tbody>
    {foreach from=$goals item=n}
    <tr class="{cycle values='r0,r1'}">
        <td class="goaltitle">
        {if $n->exists}
          <h3 class="title"><a class="goaltitle" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?id={$n->id}" id="n{$n->id}">{str tag=$n->artefacttype section='artefact.resume'}</a></h3>
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
          <h3 class="title">{str tag=$n->artefacttype section='artefact.resume'}</h3>
        {/if}
        </td>
        <td align="center">{$n->count}</td>
        <td class="right buttonscell btns2">
        {if $n->exists}
        <a id="goals_edit_{$n->artefacttype}" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?id={$n->id}" title="{str tag=edit$n->artefacttype section=artefact.resume}">
            <img src="{theme_image_url filename='btn_edit'}" alt="{str tag=edit}">
        </a>
        {else}
        <a id="goals_edit_{$n->artefacttype}" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?type={$n->artefacttype}" title="{str tag=edit$n->artefacttype section=artefact.resume}">
            <img src="{theme_image_url filename='btn_edit'}" alt="{str tag=edit}">
        </a>
        {/if}
        </td>
    </tr>
    {/foreach}
    </tbody>
</table>
{if $license}
<div class="resumelicense">
{$license|safe}
</div>
{/if}
</fieldset>