{include file="header.tpl"}
<p>{str tag=notesdescription section=artefact.internal}</p>
<table id="notes" class="fullwidth">
  <thead>
    <tr>
      <th>{str tag=Note section=artefact.internal}</th>
      <th>{str tag=currenttitle section=artefact.internal}</th>
      <th>{str tag=containedin section=artefact.internal}</th>
      <th class="center"><img src="{theme_url filename="images/attachment.png"}" title="{str tag=Attachments section=artefact.resume}" /></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$data item=n}
    <tr class="{cycle values='r0,r1'}">
      <td class="notetitle">
      {if $n->locked}
        <h3 class="title"><a class="notetitle" href="" id="n{$n->id}">{$n->title|str_shorten_text:80:true}</a></h3>
      {else}
        <h3 class="title"><a class="notetitle" href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" id="n{$n->id}">{$n->title|str_shorten_text:80:true}</a></h3>
      {/if}
        {if $n->tags}
        <div>{str tag=tags}: {list_tags tags=$n->tags owner=$n->owner}</div>
        {/if}
        <div id="n{$n->id}_desc" class="hidden detail">{$n->description|clean_html|safe}
        {if $n->files}
            <div id="notefiles_{$n->id}">
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
      </td>
      <td>
      {foreach from=$n->blocks item=b}
        <div>
          {$b.blocktitle|str_shorten_text:30:true}
        </div>
      {/foreach}
      </td>
      <td>
      {foreach from=$n->views item=v}
        <div>
          <a href="{$v.fullurl}">{$v.viewtitle|str_shorten_text:30:true}</a>
          {if $v.ownername} - {str tag=by section=view} {if $v.ownerurl}<a href="{$v.ownerurl}">{/if}{$v.ownername}{if $v.ownerurl}</a>{/if}{/if}
        </div>
        {if $v.extrablocks}
            {for i 1 $v.extrablocks}
            <div>&nbsp;</div>
            {/for}
        {/if}
      {/foreach}
      </td>
      <td align="center">{$n->count}</td>
      <td class="right buttonscell btns2">
      {if $n->locked}
        <span class="s dull">{str tag=Submitted section=view}</span>
      {else}
        <a href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" title="{str tag=edit}"><img src="{theme_url filename='images/btn_edit.png'}" alt="{str tag=edit}"></a>
        {if $n->deleteform}{$n->deleteform|safe}{/if}
      {/if}
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
{$pagination.html|safe}
{include file="footer.tpl"}
