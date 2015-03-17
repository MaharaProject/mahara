{include file="header.tpl"}

{if !$allownew}
    <div class="message info">{if $onprobation}{str tag=publicaccessnotallowedforprobation section=view}{else}{str tag=publicaccessnotallowed section=view}{/if}</div>
{/if}

{if $editurls}
<table class="secreturls fullwidth">
  <tbody>
  {foreach from=$editurls item=item name=urls}
    <tr class="{cycle values='r0,r1' advance=false}">
      <td><strong>{$item.url}</strong></td>
      <td class="right buttons btns2">
        <a id="copytoclipboard-{$item.id}" data-clipboard-text="{$item.url}" class="url-copytoclipboardbutton" title="{str tag=copytoclipboard}" href="#">
          <img src="{theme_image_url filename="btn_copy"}">
        </a>
        <a id="edit-{$item.id}" class="url-open-editform nojs-hidden-inline" title="{str tag=edit}" href="">
          <img src="{theme_image_url filename="btn_edit"}">
        </a>
        {$item.deleteform|safe}
      </td>
    </tr>
    <tr class="editrow {cycle} url-editform js-hidden" id="edit-{$item.id}-form">
      <td colspan=2>{$item.editform|safe}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}

{$newform|safe}

{include file="footer.tpl"}
