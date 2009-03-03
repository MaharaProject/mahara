{include file="header.tpl"}

{include file="columnfullstart.tpl"}
<div id="edituser" style="position: relative;">
    <div style="position: absolute; top: 0; right: 0;"><a href="{$WWWROOT}user/view.php?id={$user->id}"><img src="{$WWWROOT}thumb.php?type=profileiconbyid&amp;maxwidth=100&amp;maxheight=100&amp;id={$user->profileicon}" alt=""></a></div>
    <h2><a href="{$WWWROOT}user/view.php?id={$user->id}">{$user->firstname|escape} {$user->lastname|escape} ({$user->username|escape})</a></h2>
    {if !empty($loginas)}
      <div><a href="{$WWWROOT}admin/users/changeuser.php?id={$user->id}">{$loginas}</a></div>
    {/if}

    {if $suspended}
    <div class="message">
      <h4>{$suspendedby|escape}</h4>
      {if $user->suspendedreason}
      <div id="suspendreason">
        <h5>{str tag="suspendedreason" section="admin"}:</h5>
        {$user->suspendedreason|format_whitespace}
      </div>
      {/if}
      <div class="center">{$suspendform2}</div>
    </div>
    {/if}

    <h3>{str tag="siteaccountsettings" section="admin"}</h3>
    <p>Here you can view and set details for this user account. Below, you can also <a href="#suspend">suspend or delete this account</a>, or change settings for this user in the <a href="#institutions">institutions they are in</a>.</p>
    {$siteform}
    <!--<h3>{str tag="suspenduser" section="admin"}</h3>-->
    <hr>
    <h3 id="suspend">Suspend/Delete User</h3>
    <p>Here you may suspend or entirely delete a user account. Suspended users are unable to log in until their account is unsuspended. Please note that while a suspension can be undone, deletion <strong>cannot</strong> be undone.</p>
    <table id="suspenddelete">
        <tr>
            <td>
                <h4>Suspend User</h4>
                {$suspendform}
            </td>
            <td id="delete">
                <h4>Delete User</h4>
                <p>Please note that this operation <strong>cannot be undone</strong>.</p>
                {$deleteform}
            </td>
        </tr>
    </table>

    {if ($institutions)}
    <hr>
    <h3 id="institutions">{str tag="institutionsettings" section="admin"}</h3>
    <p>Here you can change settings regarding this user's membership with institutions in the system.</p>
    {$institutionform}
    {/if}
</div>
{include file="columnfullend.tpl"}
{include file="footer.tpl"}

