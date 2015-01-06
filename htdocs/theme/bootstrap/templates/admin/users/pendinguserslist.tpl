{if $data}
<table id="pendinglist" class="fullwidth listing">
    <thead>
        <th>{str tag=pendingregistration section=admin}</th>
        <th>{str tag=registrationreason section=admin}</th>
        <th>&nbsp;</th>
    </thead>
    <tbody>
{foreach from=$data item=registration}
      <tr class="{cycle values='r0,r1'}">
          <td class="pendinginfo">
              <div id="pendinginfo_{$registration->id}">
                <h3 class="title">{$registration->firstname} {$registration->lastname}</h3>
                <div class="detail">{$registration->email}</div>
              </div>
          </td>
          <td class="pendinginfo">
              <div class="detail">{$registration->reason}</div>
          </td>
          <td class="right">
              <a class="btn" href="{$WWWROOT}admin/users/actionregistration.php?r={$registration->id}&action=approve"><span class="btn-approve">{str tag=approve section=admin}</span></a>
              <a class="btn" href="{$WWWROOT}admin/users/actionregistration.php?r={$registration->id}&action=deny"><span class="btn-deny">{str tag=deny section=admin}</span></a>
          </td>
      </tr>
{/foreach}
{else}
  <tr><td><div class="message">{str tag=nopendingregistrations section=admin}</div></td></tr>
{/if}
