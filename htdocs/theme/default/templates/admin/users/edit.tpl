{include file="header.tpl"}

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

<div class="row">
    <div class="col-md-9">
        <h2>{str tag="siteaccountsettings" section="admin"}</h2>

        <div id="profileicon" class="profile-icon pull-left pseudolabel">
            <a class="user-icon" href="{profile_url($user)}"><img src="{profile_icon_url user=$user maxheight=100 maxwidth=100}" alt="{str tag=profileimagetext arg1=$user|display_default_name}"></a>
            <div id="profilename"><a href="{profile_url($user)}">{$user|display_name}</a></div>
            {if $loginas}
               <div id="loginas"><a class="btn btn-default" href="{$WWWROOT}admin/users/changeuser.php?id={$user->id}">{str tag=loginas section=admin}</a></div>
            {/if}
        </div>

        <p>{str tag="usereditdescription1" section="admin"}</p>
        <p class="errmsg">{str tag="usereditwarning" section="admin"}</p>

        <div class="clearfix form-group"></div>
        {$siteform|safe}

         {if ($institutions)}
        <div id="institutions" class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{str tag="institutionsettings" section="admin"}</h3>
            </div>
                <div class="panel-body">
                <p>{str tag="institutionsettingsdescription" section="admin"}</p>
                {$institutionform|safe}
            </div>
        </div>
        {/if}

    </div>

    <div class="col-md-3">

        {if $suspendable}
        <div id="suspenddelete">
            <div id="suspend" class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">{str tag="suspenduser" section=admin}</h3>
                </div>
                <div class="panel-body">
                    <p>{str tag="suspenduserdescription" section=admin}</p>
                    {$suspendform|safe}
                </div>
            </div>
            {if $deletable}
            <div id="delete" class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title">{str tag=deleteuser section=admin}</h3>
                </div>
                <div class="panel-body">
                    <p>{str tag=deleteusernote section=admin}</p>
                    {$deleteform|safe}
                </div>
            </div>
            {/if}
        </div>
        {/if}
    </div>
</div>


{include file="footer.tpl"}
