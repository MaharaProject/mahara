<div class="table-responsive">
  <table class="fullwidth table">
    <thead>
      <tr>
        <th>{str tag=Owner section=view}</th>
        <th>{str tag=View section=view}/{str tag=Collection section=collection}</th>
        <th>{str tag=accesslist section=view}</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$users item=user}
      {if !$user->views && !$user->collections}
      <tr class="{cycle values='r0,r1'}">
        <td><a href="{profile_url($user)}">{$user|display_name:null:true:true}</a></td>
        <td colspan=3>{str tag=noviews section=view}</td>
      </tr>
      {else}
        {foreach from=$user->views item=item}
      <tr class="{cycle values='r0,r1'}">
        <td><a href="{profile_url($user)}">{$user|display_name:null:true:true}</a></td>
        <td><a href="{$item.url}">{$item.name|str_shorten_text:50:true}</a></td>
        <td>{include file="admin/users/accesslistitem.tpl" item=$item}</td>
      </tr>
        {/foreach}
        {foreach from=$user->collections item=item}
      <tr class="{cycle values='r0,r1'}">
        <td><a href="{profile_url($user)}">{$user|display_name:null:true:true}</a></td>
        <td><a href="{$item.views[$item.viewid].url}">{$item.name|str_shorten_text:40:true}</a> ({str tag=nviews section=view arg1=count($item.views)})</td>
        <td>{include file="admin/users/accesslistitem.tpl" item=$item}</td>
      </tr>
        {/foreach}
      {/if}
    {/foreach}
    </tbody>
  </table>
</div>
