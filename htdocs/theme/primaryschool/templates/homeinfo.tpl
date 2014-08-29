<div id="home-info-container">
<div id="home-info" class="home-info-table{if $USER->is_logged_in()} loggedin{/if}">
<div id="homebox">
{if $USER->is_logged_in()}
   <div class="btn-findfriends"><a href="{$WWWROOT}user/find.php" alt="{str tag="findfriends" section="theme.primaryschool"}">{str tag="findfriends" section="theme.primaryschool"}</a></div>
   <div class="btn-mygroups"><a href="{$WWWROOT}group/mygroups.php" alt="{str tag="mygroups" section="theme.primaryschool"}">{str tag="mygroups" section="theme.primaryschool"}</a></div>
   <div class="btn-updateprofile"><a href="{$WWWROOT}artefact/internal/index.php" alt="{str tag="updateprofile" section="theme.primaryschool"}">{str tag="updateprofile" section="theme.primaryschool"}</a></div>
   <div class="btn-discuss"><a href="{$WWWROOT}group/topics.php" alt="{str tag="discuss" section="theme.primaryschool"}">{str tag="discuss" section="theme.primaryschool"}</a></div>
   <div class="btn-uploadfiles"><a href="{$WWWROOT}artefact/file/index.php" alt="{str tag="uploadfiles" section="theme.primaryschool"}">{str tag="uploadfiles" section="theme.primaryschool"}</a></div>
   <div class="btn-createpages"><a href="{$WWWROOT}view/index.php" alt="{str tag="createpages" section="theme.primaryschool"}">{str tag="createpages" section="theme.primaryschool"}</a></div>
   <div class="btn-writejournal"><a href="{$WWWROOT}artefact/blog/index.php" alt="{str tag="writejournal" section="theme.primaryschool"}">{str tag="writejournal" section="theme.primaryschool"}</a></div>
   <div class="btn-sharepages"><a href="{$WWWROOT}view/share.php" alt="{str tag="sharepages" section="theme.primaryschool"}">{str tag="sharepages" section="theme.primaryschool"}</a></div>
{else}
    <div class="btn-logintoexplore">{str tag="logintoexplore" section="theme.primaryschool"}</div>
{/if}
</div>
</div>
{if $USER->is_logged_in()}<p>{str tag="clickonactivity" section="theme.primaryschool"}</p>
{/if}
<div class="cb"></div>
</div>
