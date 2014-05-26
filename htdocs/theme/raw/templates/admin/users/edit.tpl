{include file="header.tpl"}

<div id="edituser">
    {if $suspended}
    <div class="suspendederror">
      <h3 class="title">{$suspendedby}</h3>
      {if $user->get('suspendedreason')}
      <div class="detail">
        <strong>{str tag="suspendedreason" section="admin"}:</strong> {$user->suspendedreason}
      </div>
      {/if}
      {$suspendform2|safe}
    </div>
    {/if}

    <div class="fullwidth" id="useraccountsettings">
        <div id="useraccountsettingsright">
            {if $suspendable}
            <div id="suspenddelete">
                <div id="suspend">
                    <h3>{str tag="suspenduser" section=admin}</h3>
                	<p>{str tag="suspenduserdescription" section=admin}</p>
                    {$suspendform|safe}
                </div>
                {if $deletable}
                <div id="delete">
                    <h3>{str tag=deleteuser section=admin}</h3>
                    <p>{str tag=deleteusernote section=admin}</p>
                    {$deleteform|safe}
                </div>
                {/if}
            </div>
            {/if}
        </div>
        <div id="useraccountsettingsleft">
            <div id="profilepict">
                <a href="{profile_url($user)}"><img src="{profile_icon_url user=$user maxheight=100 maxwidth=100}" alt="{str tag=profileimagetext arg1=$user|display_default_name}"></a>
                <div id="profilename"><a href="{profile_url($user)}">{$user|display_name}</a></div>
                {if $loginas}
                   <div id="loginas"><a class="btn" href="{$WWWROOT}admin/users/changeuser.php?id={$user->id}">{str tag=loginas section=admin}</a></div>
                {/if}
            </div>
            <h2>{str tag="siteaccountsettings" section="admin"}</h2>
            <p>{str tag="usereditdescription" section="admin"}</p>
            <p class="errmsg">{str tag="usereditwarning" section="admin"}</p>
            {$siteform|safe}
            {if ($institutions)}
            <div id="institutions">
                <h2>{str tag="institutionsettings" section="admin"}</h2>
                <p>{str tag="institutionsettingsdescription" section="admin"}</p>
                {$institutionform|safe}
            </div>
            {/if}
        </div>
        <div class="cb"></div>
    </div>
</div>

{include file="footer.tpl"}
