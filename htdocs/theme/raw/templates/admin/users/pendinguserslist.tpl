{if $data}
<table id="pendinglist" class="fullwidth table">
    <thead>
        <th>{str tag=pendingregistration section=admin}</th>
        <th>{str tag=registrationreason section=admin}</th>
        {if $extracols}
            {foreach from=$extracols key=colkey item=col}
            <th>{str tag=$colkey section=mahara}</th>
            {/foreach}
        {/if}
        <th>{str tag=expires section=admin}</th>
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
            {if $extracols}
                {foreach from=$extracols key=colkey item=col}
                <td>
                    <div class="detail">{if property_exists($registration->extra, $colkey)}{$registration->extra->$colkey}{/if}</div>
                </td>
                {/foreach}
            {/if}
            <td>{$registration->expiryformat}</td>
            <td>
                {if $registration->pending == 2}
                <div>{str tag=pendingusercompletion section=admin}</div>
                {else}
                <div class="btn-group">
                    <a class="btn btn-secondary btn-sm" href="{$WWWROOT}admin/users/actionregistration.php?r={$registration->id}&action=approve">
                        <span class="icon icon-lg left icon-check text-success" role="presentation" aria-hidden="true"></span>
                        <span class="btn-approve">{str tag=approve section=admin}</span>
                    </a>
                    <a class="btn btn-secondary btn-sm" href="{$WWWROOT}admin/users/actionregistration.php?r={$registration->id}&action=deny">
                        <span class="icon icon-lg left icon-ban text-danger" role="presentation" aria-hidden="true"></span>
                        <span class="btn-deny">{str tag=deny section=admin}</span>
                    </a>
                </div>
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
{else}
    <div class="no-results">{str tag=nopendingregistrations section=admin}</div>
{/if}
