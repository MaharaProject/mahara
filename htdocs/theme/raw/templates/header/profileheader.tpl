<div class="pageheader profileheader pageheader-actions">
    <div class="container pageheader-content">
        <div class="row">
            <div class="{if $relationship == 'pending'}col-lg-6 {else}col-md-12{/if} main">

                <h1 id="viewh1" class="user-icon-name page-header">
                    <span class="user-icon user-icon-50 float-left">
                        <img src="{profile_icon_url user=$user maxwidth=50 maxheight=50}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" />
                    </span>
                    {if $pageheadinghtml}
                    <span>{$pageheadinghtml|safe}</span>
                    {/if}
                </h1>

                <div class="userdetail view-container">
                    {if $institutions}
                    <p class="lead text-small">
                        <span class="icon icon-lg icon-university left" role="presentation" aria-hidden="true"></span>
                        {$institutions|safe}
                    </p>
                    {/if}
                    {if $invitedlist}
                    <p class="lead text-small">
                        <span class="icon icon-lg icon-users left" role="presentation" aria-hidden="true"></span>
                        {str tag=groupinvitesfrom section=group}
                        {$invitedlist}
                    </p>
                    {/if}

                    {if $requestedlist}
                    <p class="lead text-small">
                        <span class="icon icon-lg icon-users left" role="presentation" aria-hidden="true"></span>
                        {str tag=requestedmembershipin section=group}
                        {$requestedlist}
                    </p>
                    {/if}
                </div>

                {if $inviteform || $addform}
                    {if $inviteform}
                        {$inviteform|safe}
                    {/if}
                    {if $addform}
                        {$addform|safe}
                    {/if}
                {/if}

                {include file="header/profileactions.tpl"}
            </div>

            {if $relationship == 'pending'}
                <div class="pendingfriendscard col-lg-6">
                    <div class="attentionmessage card bg-warning">
                        <h3 class="card-header">{str tag='whymakemeyourfriend' section='group'}</h3>
                        <p class="card-body">{$message}</p>
                        <div class="has-form text-small card-footer">
                            <div class="btn btn-secondary">
                              <span class="icon icon-check icon-lg text-success left" role="presentation" aria-hidden="true"></span>
                              {$acceptform|safe}
                          </div>
                          <a class="btn btn-secondary" id="approve_deny_friendrequest_deny" href="{$WWWROOT}user/denyrequest.php?id={$USERID}&returnto=view">
                              <span class="icon icon-ban left icon-lg text-danger" role="presentation" aria-hidden="true"></span>
                              <span class="link-unstyled">{str tag='denyrequest' section='group'}</span>
                          </a>
                      </div>
                    </div>
                </div>
            {/if}

        </div>
    </div>
</div>
