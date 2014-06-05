<div class="as-activity">
    <a class="as-usericon" href="{$activity->primaryuserurl}" title="{$activity->primaryusername}">
        <img class="usericon" src="{profile_icon_url user=$activity->primaryuser maxheight=50 maxwidth=50}"
             alt="{str tag="editprofileicon" section="artefact.file"}" />
    </a>
    <div class="as-rightside">
        <div class="as-body">{$activity->body|safe}</div>
        <div class="as-middle">
            <ul class="as-controls">
                {foreach from=$activity->actions item=action}
                    <li class="{if $dwoo.foreach.default.index > 0}bar-before{/if}">{$action|safe}</li>
                {/foreach}
            </ul>
            <div class="as-ctime">{$activity->ctime}</div>
        </div>
        {if $activity->totallikes || $activity->comments}
            <div class="as-bottom">
                {if $activity->totallikes}{$activity->totallikes|safe}{/if}
            </div>
        {/if}
    </div>
</div>
