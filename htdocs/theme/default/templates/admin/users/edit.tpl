{include file="header.tpl"}

{include file="columnfullstart.tpl"}
<div id="edituser" style="position: relative;">
    <div style="position: absolute; top: 0; right: 0;"><a href="{$WWWROOT}user/view.php?id={$user->id}"><img src="{$WWWROOT}thumb.php?type=profileiconbyid&amp;maxwidth=100&amp;maxheight=100&amp;id={$user->profileicon}" alt=""></a></div>
    <h2><a href="{$WWWROOT}user/view.php?id={$user->id}">{$user|display_name|escape}</a></h2>
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
    <p>{str tag="usereditdescription" section="admin"}</p>
    {$siteform}
    <!--<h3>{str tag="suspenduser" section="admin"}</h3>-->
    {if $USER->get('admin') || (!$user->get('admin') && !$user->get('staff')) }
    <hr>
    <h3 id="suspend">{str tag="suspenddeleteuser" section=admin}</h3>
    <p>{str tag="suspenddeleteuserdescription" section=admin}</p>
    <table id="suspenddelete">
      <tr>
        <td>
          <h4>{str tag="suspenduser" section=admin}</h4>
          {$suspendform}
        </td>
        {if $USER->get('admin')}
        <td id="delete">
          <h4>{str tag=deleteuser section=admin}</h4>
          <p>{str tag=deleteusernote section=admin}</p>
          {$deleteform}
        </td>
        {/if}
      </tr>
    </table>
    {/if}

    {if ($institutions)}
    <hr>
    <h3 id="institutions">{str tag="institutionsettings" section="admin"}</h3>
    <p>{str tag="institutionsettingsdescription" section="admin"}</p>
    {$institutionform}
    {/if}
</div>
{include file="columnfullend.tpl"}
{include file="footer.tpl"}

