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
                    <h3>{$registration->firstname} {$registration->lastname}</h3>
                    <p>{$registration->email}</p>
              </div>
          </td>
          <td class="pendinginfo">
                  <p>{$registration->reason}</p>
          </td>
          <td class="right">
              <a class="btn" href="{$WWWROOT}admin/users/actionregistration.php?r={$registration->id}&action=approve">{str tag=approve section=admin}</a>
                <a class="btn" href="{$WWWROOT}admin/users/actionregistration.php?r={$registration->id}&action=deny">{str tag=deny section=admin}</a>
          </td>
      </tr>
{/foreach}
{else}
  <tr><td><div class="message">{str tag=nopendingregistrations section=admin}</div></td></tr>
{/if}
