<div class="as-activity">
    <div class="as-topsection">
        <a href="{$activity->primaryuserurl}" title="{$activity->primaryusername}">
            <img class="usericon" src="{profile_icon_url user=$activity->primaryuser maxheight=50 maxwidth=50}"
                 alt="{str tag="editprofileicon" section="artefact.file"}" />
        </a>
        <div class="as-toprightsection">
            {if $activity->body}
                <div>{$activity->body|safe}</div>
            {/if}
            <div class="as-ctime">{$activity->ctime}</div>
        </div>
    </div>
</div>
