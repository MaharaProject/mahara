<div class="friendcell panel-body">
    <div class="row">
        {foreach from=$groupmembers item=groupmember}
        <div class="col-xs-6 col-md-3 userthumb">
        <a href="{profile_url($groupmember)}" class="thumbnail" height="130px">
           <img src="{profile_icon_url user=$groupmember maxwidth=60 maxheight=60}" alt="{str tag=profileimagetext arg1=$groupmember|display_default_name}" title="{$groupmember|display_default_name|escape}">
           <span class="metadata">{$groupmember|display_default_name|escape}</span>
        </a>
        </div>
        {/foreach}
    </div>
</div>
