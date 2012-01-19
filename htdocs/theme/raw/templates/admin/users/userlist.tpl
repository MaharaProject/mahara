<table class="fullwidth">
  <thead>
    <tr>
      <th>{str tag=username}</th>
      <th>{str tag=email}</th>
      <th>{str tag=firstname}</th>
      <th>{str tag=lastname}</th>
      <th>{str tag=studentid}</th>
      <th>{str tag=preferredname}</th>
      <th>{str tag=remoteuser section=admin}</th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$users item=user}
    <tr class="{cycle values='r0,r1'}">
      <td>{$user->username}</td>
      <td>{$user->email}</td>
      <td>{$user->firstname}</td>
      <td>{$user->lastname}</td>
      <td>{$user->studentid}</td>
      <td>{$user->preferredname}</td>
      <td>{$user->remoteuser}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
