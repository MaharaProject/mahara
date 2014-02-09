<div id="home-info-container">
    {if $USER->is_logged_in()}
        <div id="hideinfo" class="nojs-hidden-block">
            <a href="#" title="{str tag=Hide2}">
                <img src="{theme_url filename='images/btn_close.png'}" alt="{str tag=Close}" />
            </a>
        </div>
    {/if}
<table class="home-info-table{if $USER->is_logged_in()} loggedin{/if} fullwidth">
    <tr>
        <th class="home-info home-info-1">
            <h2 class="title">{str tag=createcollect}</h2>
        </th>
        <th class="home-info home-info-2">
            <h2 class="title">{str tag=Organise}</h2>
        </th>
        <th class="home-info home-info-3">
            <h2 class="title">{str tag=sharenetwork}</h2>
        </th>
    </tr>
    <tr>
        <td class="home-info home-info-1">
            <p class="subtitle">{str tag=createcollectsubtitle}</p>
        </td>
        <td class="home-info home-info-2">
            <p class="subtitle">{str tag=organisesubtitle}</p>
        </td>
        <td class="home-info home-info-3">
            <p class="subtitle">{str tag=sharenetworksubtitle}</p>
        </td>
    </tr>
    <tr>
        <td class="home-info home-info-1">
            <div class="home-info-grid fullwidth">
                <div>
                    <div>
                        {if $USER->is_logged_in()}<a href="{$url.profile}">{/if}
                        <img src="{theme_url filename='images/dashboard_profile.png'}" alt="" />
                        <span class="caption">
                            {str tag=updateprofile arg1=$url.profile}
                        </span>
                        {if $USER->is_logged_in()}</a>{/if}
                    </div>
                    <div>
                        {if $USER->is_logged_in()}<a href="{$url.files}">{/if}
                        <img src="{theme_url filename='images/dashboard_files.png'}" alt="" />
                        <span class="caption">
                            {str tag=uploadfiles arg1=$url.files}
                        </span>
                        {if $USER->is_logged_in()}</a>{/if}
                    </div>
                </div>
                <div>
                    <div>
                        {if $USER->is_logged_in()}<a href="{$url.resume}">{/if}
                        <img src="{theme_url filename='images/dashboard_resume.png'}" alt="" />
                        <span class="caption">
                            {str tag=createresume arg1=$url.resume}
                        </span>
                        {if $USER->is_logged_in()}</a>{/if}
                    </div>
                    <div>
                        {if $USER->is_logged_in()}<a href="{$url.blog}">{/if}
                        <img src="{theme_url filename='images/dashboard_journal.png'}" alt="" />
                        <span class="caption">
                            {str tag=publishblog arg1=$url.blog}
                        </span>
                        {if $USER->is_logged_in()}</a>{/if}
                    </div>
                </div>
            </div>
        </td>
        <td class="home-info home-info-2">
            <div>
                {if $USER->is_logged_in()}<a href="{$url.views}">{/if}
                    <img class="organise" src="{theme_url filename='images/dashboard_organise.png'}" alt="{str tag=views}" />
                {if $USER->is_logged_in()}</a>{/if}
            </div>
            <p class="desc">
                {assign var=s value="organisedescription"|str:mahara:$url.views}
                {if $USER->is_logged_in()}{$s|safe}{else}{$s|safe|strip_tags}{/if}
            </p>
        </td>
        <td class="home-info home-info-3">
            <div class="home-info-grid fullwidth">
                <div>
                    <div>
                        {if $USER->is_logged_in()}<a href="{$url.friends}">{/if}
                        <img src="{theme_url filename='images/dashboard_friends.png'}" alt="" />
                        <span class="caption">
                            {str tag=findfriends arg1=$url.friends}
                        </span>
                        {if $USER->is_logged_in()}</a>{/if}
                    </div>
                    <div>
                        {if $USER->is_logged_in()}<a href="{$url.groups}">{/if}
                        <img src="{theme_url filename='images/dashboard_groups.png'}" alt="" />
                        <span class="caption">
                            {str tag=joinsomegroups arg1=$url.groups}
                        </span>
                        {if $USER->is_logged_in()}</a>{/if}
                    </div>
                </div>
                <div>
                    <div>
                        {if $USER->is_logged_in()}<a href="{$url.share}">{/if}
                        <img src="{theme_url filename='images/dashboard_share.png'}" alt="" />
                        <span class="caption">
                            {str tag=controlyourprivacy arg1=$url.share}
                        </span>
                        {if $USER->is_logged_in()}</a>{/if}
                    </div>
                    <div>
                        {if $USER->is_logged_in()}<a href="{$url.topics}">{/if}
                        <img src="{theme_url filename='images/dashboard_topics.png'}" alt="" />
                        <span class="caption">
                            {str tag=discusstopics arg1=$url.topics}
                        </span>
                        {if $USER->is_logged_in()}</a>{/if}
                    </div>
                </div>
            </div>
        </td>
    </tr>
</table>
</div>
