{include file="header.tpl"}
<p class="lead">{str tag="usereditdescription1" section="admin"}</p>

{if $suspended}
    <div class="suspendederror admin-warning alert alert-warning">
        <h2 class="title">{$suspendedby}</h2>
        {if $user->get('suspendedreason')}
            <div class="detail">
                <strong>{str tag="suspendedreason" section="admin"}: </strong>
                {$user->suspendedreason}
            </div>
        {/if}
        {$suspendform2|safe}
   </div>
{elseif $expired}
    <div class="suspendederror admin-warning alert alert-warning">
        <h2 class="title">{$expiredon}</h2>
        <div class="detail">
            {str tag=unexpiredesc section=admin}
        </div>
   </div>
{/if}
    <div class="row">
        <div class="col-lg-9 main">
            <div class="card card-body">
                <h2>{str tag="siteaccountsettings" section="admin"}</h2>
                <p class="errmsg">{str tag="usereditwarning1" section="admin"}</p>
                {$siteform|safe}
                {if $institutions > 1}
                   {$institutionform|safe}
                {/if}
            </div>
        </div>

        <div class="col-md-3 sidebar">
            <div class="sideblock-1 user-card">
                <div class="card">
                    <h2 class="card-header profile-block">
                        <a href="{profile_url($user)}" class="username">
                            {$user|display_name}
                        </a>
                        <a href="{profile_url($user)}" class="user-icon user-icon-60">
                            <img src="{profile_icon_url user=$user maxheight=60 maxwidth=60}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
                        </a>
                    </h2>
                    <div class="card-body">
                    {if $loginas}
                       <p id="loginas" class="loginas">
                           <a href="{$WWWROOT}admin/users/changeuser.php?id={$user->id}">
                               {str tag=loginasthisuser section=admin}
                           </a>
                       </p>
                    {/if}
                    {if $suspendable && $deletable}
                        <a href="" data-toggle="modal" data-target="#suspenddeletemodal">
                            {str tag=suspendordeletethisuser section=admin}
                        </a>
                    {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal -->
<div class="modal fade" id="suspenddeletemodal" tabindex="-1" role="dialog" aria-labelledby="suspenddeletemodaltitle">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{str tag=Close}">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h1 id="suspenddeletemodaltitle" class="modal-title">{str tag=suspendordeletethisuser section=admin}</h1>
            </div>
            <div class="modal-body">
                {if $suspendable}
                    <div id="suspenddelete">
                        <div id="suspend">
                            <h2>{str tag="suspenduser" section=admin}</h2>
                            <p>{str tag="suspenduserdescription" section=admin}</p>
                            {$suspendform|safe}
                        </div>
                    </div>
                {/if}
                {if $deletable}
                    <div id="delete">
                        <h2>{str tag=deleteuser section=admin}</h2>
                        <p>{str tag=deleteusernote section=admin}</p>
                        {$deleteform|safe}
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}
