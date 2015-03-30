{include file="header.tpl"}
<p>{str tag=allowediframesitesdescription section=admin}</p>
<p>{str tag=allowediframesitesdescriptiondetail section=admin}</p>

{if $editurls}
<table class="iframesources fullwidth">
  <thead>
    <tr>
      <th>{str tag=Site}</th>
      <th>{str tag=displayname}</th>
      <th><span class="accessible-hidden">{str tag=edit}</span></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$editurls item=item name=urls}
    <tr class="{cycle values='r0,r1' advance=false}">
      <td><strong>{$item.url}</strong></td>
      <td><img src="{$item.icon}" alt="{$item.name}" title="{$item.name}">&nbsp;{$item.name}</td>
      <td class="right buttonscell btns2">
        <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline" title="{str tag=edit}" href="">
          <img src="{theme_image_url filename="btn_edit"}" alt="{str(tag=editspecific arg1=$item.name)|escape:html|safe}">
        </a>
        {$item.deleteform|safe}
      </td>
    </tr>
    <tr class="editrow {cycle} url-editform js-hidden" id="edit-{$item.id}-form">
      <td colspan=3>{$item.editform|safe}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}

{$newform|safe}

{include file="footer.tpl"}
