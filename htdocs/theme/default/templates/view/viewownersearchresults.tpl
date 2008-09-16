<table class="templateresults tablerenderer">
  <thead>
    <tr>
      <th>{str tag=name}</th>
      <th>{str tag=views}</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
{if !empty($results.data)}
{foreach from=$results.data item=row}
    <tr class="r{cycle values=0,1}">
      <td>
          {if $row->ownertype == 'institution'}
          {$row->display|escape}
          {elseif $row->ownertype == 'group'}
          <a href="{$WWWROOT}group/view.php?id={$row->id|escape}" target="_blank">{$row->display|escape}</a>
          {else}
          <a href="{$WWWROOT}user/view.php?id={$row->id|escape}" target="_blank">{$row->display|escape}</a>
          {/if}
      </td>
      <td style="text-align: center;">{$row->count|escape}</td>
      <td class="selectowner"><a href="{$viewurl}&amp;owntype={$row->ownertype}&amp;ownid={$row->id}">{str tag=listviews section=view}</a>&nbsp;<img src="{theme_path location='images/icon_fieldset_left.gif'}" alt=""></td>
    </tr>
{/foreach}
{else}
    <tr><td colspan="3">{str tag="noownersfound" section=view}</td></tr>
{/if}
  </tbody>
</table>
