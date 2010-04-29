<table id="home-info" class="home-info-table{if $USER->is_logged_in()} loggedin{/if}">
    <tr>
        <td>
{if $USER->is_logged_in()}
            <a id="hideinfo" title="Hide"><img src="{theme_url filename='images/icon_close.gif'}" alt="[x]" /></a>
{/if}
            <div class="home-info">
                <h3>Create and Collect</h3>
                <p class="subtitle">Develop your portfolio</p>
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
                    <tr class="caption">
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
                    <tr class="caption">
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
                <h3>Organise</h3>
                <p class="subtitle">Showcase your portfolio with views</p>
                <div>
                    {if $USER->is_logged_in()}<a href="/view">{/if}
                        <img class="organise" src="{theme_url filename='images/organise.png'}" alt="" />
                    {if $USER->is_logged_in()}</a>{/if}
                </div>
                <p class="desc">
                Organise your portfolio into
                {if $USER->is_logged_in()}<a href="/view">{/if}
                    Views.
                {if $USER->is_logged_in()}</a>{/if}
                Create different views for different audiences - you choose the elements to include.
                </p>
            </div>
        </td>
        <td>
            <div class="home-info">
                <h3>Share and Network</h3>
                <p class="subtitle">Meet friends and join groups</p>
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
                <p id="accessdesc" class="desc">
                    You can fine-tune who has access to each view, and for how long.
                </p>
            </div>
        </td>
    </tr>
</table>
