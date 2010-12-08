<div id="home-info-container">
<table id="home-info" class="home-info-table{if $USER->is_logged_in()} loggedin{/if}">
    <tr>
        <td>
            <div class="home-info rel home-info-1">
            <div class="home-info-inner">
                <div>
                    <h3>{str tag=createcollect}</h3>
                </div>
                <p class="subtitle">{str tag=createcollectsubtitle}</p>
                <table>
                    <tr>
                        <td>
                            {if $USER->is_logged_in()}<a href="{$url.profile}">{/if}
                                <img src="{theme_url filename='images/profile.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                            <div class="caption">{assign var=s value="updateyourprofile"|str:mahara:$url.profile}
                            {if $USER->is_logged_in()}{$s|safe}{else}{$s|safe|strip_tags}{/if}</div>
                        </td>
                        <td>
                            {if $USER->is_logged_in()}<a href="{$url.files}">{/if}
                                <img src="{theme_url filename='images/files.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                            <div class="caption">{assign var=s value="uploadyourfiles"|str:mahara:$url.files}
                            {if $USER->is_logged_in()}{$s|safe}{else}{$s|safe|strip_tags}{/if}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {if $USER->is_logged_in()}<a href="{$url.resume}">{/if}
                                <img src="{theme_url filename='images/resume.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                            <div class="caption">{assign var=s value="createyourresume"|str:mahara:$url.resume}
                            {if $USER->is_logged_in()}{$s|safe}{else}{$s|safe|strip_tags}{/if}</div>
                        </td>
                        <td>
                            {if $USER->is_logged_in()}<a href="{$url.blog}">{/if}
                                <img src="{theme_url filename='images/blog.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                            <div class="caption">{assign var=s value="publishablog"|str:mahara:$url.blog}
                            {if $USER->is_logged_in()}{$s|safe}{else}{$s|safe|strip_tags}{/if}</div>
                        </td>
                    </tr>
                </table>
            </div>
            </div>
        </td>
        <td>
            <div class="home-info home-info-2">
            <div class="home-info-inner">
                <h3>{str tag=Organise}</h3>
                <p class="subtitle">{str tag=organisesubtitle}</p>
                <div>
                    {if $USER->is_logged_in()}<a href="{$url.views}">{/if}
                        <img class="organise" src="{theme_url filename='images/organise.png'}" alt="" />
                    {if $USER->is_logged_in()}</a>{/if}
                </div>
                <p class="desc">
                    {assign var=s value="organisedescription"|str:mahara:$url.views}
                    {if $USER->is_logged_in()}{$s|safe}{else}{$s|safe|strip_tags}{/if}
                </p>
            </div>
            </div>
        </td>
        <td>
            {if $USER->is_logged_in()}<div id="hideinfo" class="nojs-hidden-block"><a title="{str tag=Hide}"><img src="{theme_url filename='images/icon_close.gif'}" alt="[x]" /></a></div>{/if}
            <div class="home-info home-info-3">
            <div class="home-info-inner">
                <h3>{str tag=sharenetwork}</h3>
                <p class="subtitle">{str tag=sharenetworksubtitle}</p>
                <table>
                    <tr>
                        <td>
                            {if $USER->is_logged_in()}<a href="{$url.friends}">{/if}
                                <img src="{theme_url filename='images/friends.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                            <div class="caption">{assign var=s value="findfriendslinked"|str:mahara:$url.friends}
                            {if $USER->is_logged_in()}{$s|safe}{else}{$s|safe|strip_tags}{/if}</div>
                        </td>
                        <td>
                            {if $USER->is_logged_in()}<a href="{$url.groups}">{/if}
                                <img src="{theme_url filename='images/groups.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                            <div class="caption">{assign var=s value="joingroups"|str:mahara:$url.groups}
                            {if $USER->is_logged_in()}{$s|safe}{else}{$s|safe|strip_tags}{/if}</div>
                        </td>
                    </tr>
                </table>
                <p id="accessdesc">{str tag=sharenetworkdescription}</p>
            </div>
            </div>
        </td>
    </tr>
</table>
</div>
