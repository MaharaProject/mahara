<div class="table-responsive">
  <table class="fullwidth table table-striped">
    <thead>
      <tr>
        <th>{str tag=username}</th>
        {if $USER->get('admin') || $USER->is_institutional_admin()}<th>{str tag=email}</th>{/if}
        <th>{str tag=firstname}</th>
        <th>{str tag=lastname}</th>
        <th>{str tag=studentid}</th>
        <th>{str tag=preferredname}</th>
        {if $USER->get('admin') || $USER->is_institutional_admin()}<th>{str tag=remoteuser section=admin}</th>{/if}
        <th>{str tag=lastlogin section=admin}</th>
        {if is_using_probation()}<th>{str tag=probationreportcolumn section=admin}</th>{/if}
      </tr>
    </thead>
    <tbody>
    {foreach from=$users item=user}
      <tr class="{cycle values='r0,r1'}">
        <td>{$user->username}</td>
        {if $USER->get('admin') || $USER->is_institutional_admin()}<td>{if $user->hideemail}<span class="dull">({str tag=hidden})</span>{else}{$user->email}{/if}</td>{/if}
        <td>{$user->firstname}</td>
        <td>{$user->lastname}</td>
        <td>{$user->studentid}</td>
        <td>{$user->preferredname}</td>
        {if $USER->get('admin') || $USER->is_institutional_admin()}<td>{if $user->hideemail}<span class="dull">({str tag=hidden})</span>{else}{$user->remoteuser}{/if}</td>{/if}
        <td>{if $user->lastlogin}{$user->lastlogin|strtotime|format_date:'strftimedatetime'}{/if}</td>
        {if is_using_probation()}<td>{$user->probation}</td>{/if}
      </tr>
    {/foreach}
    </tbody>
  </table>
</div>
