{include file="header.tpl"}

{if $relationship == 'pending'}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="expose">
    <div class="row">
        <div class="col-md-4  col-md-offset-4">
            <div class="attentionmessage panel panel-warning">
                <h3 class="panel-heading">{str tag='whymakemeyourfriend' section='group'}</h3>
                <p class="panel-body">{$message}</p>
                <div class="has-form text-small panel-footer btn-toggle">
                    <div class="btn">
                        <span class="icon icon-check icon-lg text-success prs"></span>
                        {$acceptform|safe}
                    </div>
                    <a class="btn" id="approve_deny_friendrequest_deny" href="{$WWWROOT}user/denyrequest.php?id={$USERID}&returnto=view">
                        <span class="icon icon-ban prs icon-lg text-danger"></span>
                        <span class="link-unstyled">{str tag='denyrequest' section='group'}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="{if $SIDEBARS}{if $SIDEBLOCKS.right}col-md-9 {else}col-md-9 col-md-push-3{/if}{else}col-md-12{/if} main">
            <div class="main-column{if $selected == 'content'} editcontent{/if}">
                <div>
{/if}

<h1 class="user-icon-name page-header">
    <span class="user-icon mrl mlm pull-left">
        <img src="{profile_icon_url user=$user maxwidth=50 maxheight=50}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" />
    </span>
    {if $pageheadinghtml}
    <span class="ptm">{$pageheadinghtml|safe}</span>
    {/if}

</h1>

<div class="btn-group btn-group-top">
    {if $ownprofile}
        <a title="{str tag=editthisview section=view}" href="{$WWWROOT}view/blocks.php?profile=1" class="btn btn-default">
            <span class="icon-pencil icon icon-lg prs"></span>
            {str tag=editthisview section=view}
        </a>
    {/if}
    {if $loginas}
        <a href="{$WWWROOT}admin/users/changeuser.php?id={$USERID}" class="btn-login btn btn-default">
            <span class="icon-user-secret icon icon-lg prs"></span>
            {$loginas}
        </a>
        
        {if $USER->get('admin')}
        <a href="{$WWWROOT}admin/users/edit.php?id={$USERID}" class="btn-edit btn btn-default">
            <span class="icon-cogs icon icon-lg prs"></span>
            {str tag=accountsettings section=admin}
        </a>
        {/if}
    {/if}
    
    {if $canmessage}
    <a href="{$WWWROOT}user/sendmessage.php?id={$USERID}&amp;returnto=view" class="btn-message btn btn-default">
        <span class="icon-envelope icon icon-lg prs"></span>
        {str tag='sendmessage' section='group'}
    </a>
    {/if}
    
    {if $relationship == 'existingfriend'}
        <a href="{$WWWROOT}user/removefriend.php?id={$USERID}&amp;returnto=view" class="btn-del btn btn-default">
             <span class="icon-user-times icon icon-lg prs text-danger"></span>
            {str tag='removefromfriendslist' section='group'}
        </a>
    
    {elseif $relationship == 'none' && $friendscontrol == 'auto'}
    
    {$newfriendform|safe}
    
    {elseif $relationship == 'none' && $friendscontrol == 'auth'}
    <a href="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view" class="btn-friend btn btn-default">
        <span class="icon-user-plus icon icon-lg prs text-success"></span>
        {str tag='requestfriendship' section='group'}
    </a>
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

<div class="row">
    <div class="col-md-8 ptxl">
        {if $institutions}
        <p class="pll lead text-small">
            <span class="icon icon-lg icon-university  prs"></span>
            {$institutions|safe}
        </p>
        {/if}
        {if $invitedlist}
        <p class="pll lead text-small">
            <span class="icon icon-lg icon-users prs"></span>
            {str tag=groupinvitesfrom section=group}
            {$invitedlist}
        </p>
        {/if}

        {if $requestedlist}
        <p class="pll lead text-small">
            <span class="icon icon-lg icon-users prs"></span>
            {str tag=requestedmembershipin section=group}
            {$requestedlist}
        </p>
        {/if}
    </div>
</div>

<div id="view" class="cl">
    <div id="bottom-pane">
        <div id="column-container">
            {if $restrictedview}
            <strong>{str tag=profilenotshared section=view}</strong>
            {else}
            
            {$viewcontent|safe}
            
            {/if}
        </div>
    </div>
</div>

{include file="footer.tpl"}

