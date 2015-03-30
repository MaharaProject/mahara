{include file="header.tpl"}
<p>{str tag=notesdescription1 section=artefact.internal}</p>
<table id="notes" class="fullwidth listing">
  <thead>
    <tr>
      <th>{str tag=Note section=artefact.internal}</th>
      <th>{str tag=currenttitle section=artefact.internal}</th>
      <th>{str tag=containedin section=artefact.internal}</th>
      <th class="center"><img src="{theme_image_url filename="attachment"}" title="{str tag=Attachments section=artefact.resume}" alt="{str tag=Attachments section=artefact.resume}" /></th>
      <th><span class="accessible-hidden">{str tag=edit}</span></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$data item=n}
    <tr class="{cycle values='r1,r0'}">
      <td class="note-name">
      {if $n->locked}
        <h3 class="title"><a class="notetitle" href="" id="n{$n->id}">{$n->title|str_shorten_text:80:true} <span class="accessible-hidden">{str tag=clickformore}</span></a></h3>
      {else}
        <h3 class="title"><a class="notetitle" href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" id="n{$n->id}">{$n->title|str_shorten_text:80:true} <span class="accessible-hidden">{str tag=clickformore}</span></a></h3>
      {/if}
       <div id="n{$n->id}_desc" class="hidden detail">{$n->description|clean_html|safe}
            {if $n->files}
            <div id="notefiles_{$n->id}">
                <table class="attachments fullwidth">
                    <thead class="expandable-head">
                        <tr>
                            <td colspan="2">
                                <a class="toggle" href="#">{str tag=attachedfiles section=artefact.blog}</a>
                                <span class="fr">
                                    <img class="fl" src="{theme_image_url filename='attachment'}" alt="{str tag=attachments section=artefact.blog}">
                                    {$n->files|count}
                                </span>
                            </td>
                        </tr>
                    </thead>
                    <tbody class="expandable-body">
                        {foreach from=$n->files item=file}
                            <tr class="{cycle values='r1,r0'}">
                                <td class="icon-container"><img src="{$file->icon}" alt=""></td>
                                <td class="valign">
                                    <div><a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}">{$file->title}</a></div>
                                    <div class="detail s">{$file->description}</div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            {/if}
       </div>
      {if $n->tags}
        <div class="tags">{str tag=tags}: {list_tags tags=$n->tags owner=$n->owner}</div>
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
      <td class="note-attachment"><label class="hidden">{str tag=Attachments section=artefact.resume}: </label> {$n->count}</td>
      <td class="right buttonscell btns2">
      {if $n->locked}
        <span class="s dull">{str tag=Submitted section=view}</span>
      {else}
        <a href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" title="{str tag=edit}"><img src="{theme_image_url filename='btn_edit'}" alt="{str(tag=editspecific arg1=$n->title)|escape:html|safe}"></a>
        {if $n->deleteform}{$n->deleteform|safe}{/if}
      {/if}
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
{$pagination.html|safe}
{include file="footer.tpl"}
