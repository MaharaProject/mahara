{include file="header.tpl"}
<p>{str tag=notesdescription section=artefact.internal}</p>
<table id="notes" class="fullwidth">
  <thead>
    <tr>
      <th>{str tag=Note section=artefact.internal}</th>
      <th>{str tag=containedin section=artefact.internal}</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$data item=n}
    {assign var=shortdescription value=$n->description|str_shorten_html:100:true|safe}
    <tr class="{cycle values=r0,r1}">
      <td class="notetitle">
      {if $n->locked}
        <h4><a class="notetitle" href="" id="n{$n->id}">{$n->title|str_shorten_text:80:true|safe}</a></h4>
      {else}
        <h4><a class="notetitle" href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" id="n{$n->id}">{$n->title|str_shorten_text:80:true|safe}</a></h4>
      {/if}
        <div id="n{$n->id}_desc" class="hidden desc">{$n->description|clean_html|safe}</div>
      </td>
      <td>
      {foreach from=$n->views item=v}
        <div>
          <a href="{$v.fullurl}">{$v.viewtitle|str_shorten_text:30:true}</a>
          {if $v.ownername} - {str tag=by section=view} {if $v.ownerurl}<a href="{$v.ownerurl}">{/if}{$v.ownername}{if $v.ownerurl}</a>{/if}{/if}
        </div>
      {/foreach}
      </td>
      <td class="right buttonscell btns2">
      {if $n->locked}
        <span class="s dull">{str tag=Submitted section=view}</span>
      {else}
        <a href="{$WWWROOT}artefact/internal/editnote.php?id={$n->id}" title="{str tag=edit}"><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a>
        {if $n->deleteform}{$n->deleteform|safe}{/if}
      {/if}
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
{$pagination.html|safe}
{include file="footer.tpl"}
