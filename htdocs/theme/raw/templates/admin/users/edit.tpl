{include file="header.tpl"}
<p class="lead">{str tag="usereditdescription" section="admin"}</p>

{if $suspended}
    <div class="suspendederror admin-warning alert alert-warning">
        <h3 class="title">{$suspendedby}</h3>
        {if $user->get('suspendedreason')}
            <div class="detail mbl">
                <strong>{str tag="suspendedreason" section="admin"}: </strong>
                {$user->suspendedreason}
            </div>
        {/if}
        {$suspendform2|safe}
   </div>
{/if}
    <div class="row">
        <div class="col-md-9 main">
            <div class="panel panel-body">
                <h2>{str tag="siteaccountsettings" section="admin"}</h2>
                <p class="errmsg">{str tag="usereditwarning" section="admin"}</p>
                {$siteform|safe}
                {if ($institutions)}
                   {$institutionform|safe}
                {/if}
            </div>
        </div>

        <div class="col-md-3">
            <div class="user-panel">
                <div class="panel panel-default">
                    <h3 class="panel-heading profile-block">
                        <a href="{profile_url($user)}" class="username">
                            {$user|display_name}
                        </a>
                        <a href="{profile_url($user)}" class="user-icon">
                            <img src="{profile_icon_url user=$user maxheight=100 maxwidth=100}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
                        </a>
                    </h3>
                    {if $loginas}
                       <div id="loginas">
                           <a class="btn btn-link" href="{$WWWROOT}admin/users/changeuser.php?id={$user->id}">
                               {str tag=loginasthisuser section=admin}
                           </a>
                       </div>
                    {/if}
                    <div class="list-group">
                    {if $suspendable && $deletable}

                        <button type="button" class="btn btn-link" data-toggle="modal" data-target="#suspenddeletemodal">
                             {str tag=suspendordeletethisuser section=admin}
                        </button>

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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 id="suspenddeletemodaltitle" class="modal-title">{str tag=suspendordeletethisuser section=admin}</h4>
            </div>
            <div class="modal-body">
                {if $suspendable}
                    <div id="suspenddelete">
                        <div id="suspend">
                            <h3>{str tag="suspenduser" section=admin}</h3>
                            <p>{str tag="suspenduserdescription" section=admin}</p>
                            {$suspendform|safe}
                        </div>
                    </div>
                {/if}
                {if $deletable}
                    <div id="delete">
                        <h3>{str tag=deleteuser section=admin}</h3>
                        <p>{str tag=deleteusernote section=admin}</p>
                        {$deleteform|safe}
                    </div>
                {/if}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}
