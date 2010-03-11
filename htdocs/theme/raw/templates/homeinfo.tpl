<div id="home-info" class="rel">
{if $USER->is_logged_in()}
<div class="rbuttons"><a id="hideinfo" class="btn-del">Don't show this</a></div>
{/if}
<table class="home-info-table">
    <tr>
        <td>
            <div class="home-info">
                <h3 class="center">Create and Collect</h3>
                <p class="subtitle center">Develop your portfolio</p>
                <table>
                    <tr>
                        <td>
                            {if $USER->is_logged_in()}<a href="/artefact/internal">{/if}
                                <img src="{theme_url filename='images/profile.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                        </td>
                        <td>
                            {if $USER->is_logged_in()}<a href="/artefact/file">{/if}
                                <img src="{theme_url filename='images/files.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Update your
                            {if $USER->is_logged_in()}<a href="/artefact/internal">{/if}
                                Profile
                            {if $USER->is_logged_in()}</a>{/if}
                        </td>
                        <td>
                            Upload your
                            {if $USER->is_logged_in()}<a href="/artefact/file">{/if}
                                Files
                            {if $USER->is_logged_in()}</a>{/if} 
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {if $USER->is_logged_in()}<a href="/artefact/resume">{/if}
                                <img src="{theme_url filename='images/resume.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                        </td>
                        <td>
                            {if $USER->is_logged_in()}<a href="/artefact/blog">{/if}
                                <img src="{theme_url filename='images/blog.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Create your
                            {if $USER->is_logged_in()}<a href="/artefact/resume">{/if}
                                Resume
                            {if $USER->is_logged_in()}</a>{/if}
                        </td>
                        <td>
                            Publish a
                            {if $USER->is_logged_in()}<a href="/artefact/blog">{/if}
                                Blog
                            {if $USER->is_logged_in()}</a>{/if} 
                        </td>
                    </tr>
                </table>
            </div>
        </td>
        <td>
            <div class="home-info">
                <h3 class="center">Organise</h3>
                <p class="subtitle center">Showcase your portfolio with views</p>
                <div class="center">
                    {if $USER->is_logged_in()}<a href="/view">{/if}
                        <img class="organise" src="{theme_url filename='images/organise.png'}" alt="" />
                    {if $USER->is_logged_in()}</a>{/if}
                </div>
                Organise your portfolio into
                {if $USER->is_logged_in()}<a href="/view">{/if}
                    Views.
                {if $USER->is_logged_in()}</a>{/if}
                Create different views for different audiences - you choose the elements to include.
            </div>
        </td>
        <td>
            <div class="home-info">
                <h3 class="center">Share and Network</h3>
                <p class="subtitle center">Meet friends and join groups</p>
                <table>
                    <tr>
                        <td>
                            {if $USER->is_logged_in()}<a href="/user/find.php">{/if}
                                <img src="{theme_url filename='images/friends.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                        </td>
                        <td>
                            {if $USER->is_logged_in()}<a href="/group/find.php">{/if}
                                <img src="{theme_url filename='images/groups.png'}" alt="" />
                            {if $USER->is_logged_in()}</a>{/if}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Find
                            {if $USER->is_logged_in()}<a href="/user/find.php">{/if}
                                Friends
                            {if $USER->is_logged_in()}</a>{/if}
                        </td>
                        <td>
                            Join
                            {if $USER->is_logged_in()}<a href="/group/find.php">{/if}
                                Groups
                            {if $USER->is_logged_in()}</a>{/if} 
                        </td>
                    </tr>
                </table>
                <div id="accessdesc">
                    You can fine-tune who has access to each view, and for how long.
                </div>
            </div>
        </td>
    </tr>
</table>
</div>
