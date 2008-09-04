<table class="templateresults tablerenderer">
  <thead>
    <th>{str tag=name}</th>
    <th>{str tag=views}</th>
    <th></th>
  </thead>
  <tbody>
{if !empty($results.data)}
{foreach from=$results.data item=row}
    <tr class="r{cycle values=0,1}">
      <td>
          {if $row->type == 'institution'}
          {$row->display|escape}
          {elseif $row->type == 'group'}
          <a href="{$WWWROOT}group/view.php?id={$row->id|escape}">{$row->display|escape}</a>
          {else}
          <a href="{$WWWROOT}user/view.php?id={$row->id|escape}">{$row->display|escape}</a>
          {/if}
      </td>
      <td style="text-align: center;">{$row->count|escape}</td>
      <td><a href="{$viewurl}&amp;owntype={$row->type}&amp;ownid={$row->id}">{str tag=listviews section=view}</a>&nbsp;<img src="{theme_path location='images/icon_fieldset_left.gif'}" alt=""></td>
    </tr>
{/foreach}
{else}
    <tr><td colspan="3">{str tag="noownersfound" section=view}</td></tr>
{/if}
  </tbody>
</table>
