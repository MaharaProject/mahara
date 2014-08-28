<div id="home-info-container">
<div id="home-info" class="home-info-table{if $USER->is_logged_in()} loggedin{/if}">
<div id="homebox">
{if $USER->is_logged_in()}
   <div class="btn-findfriends"><a href="{$WWWROOT}user/find.php" alt="Find Friends">Find Friends</a></div>
   <div class="btn-mygroups"><a href="{$WWWROOT}group/mygroups.php" alt="My Groups">My Groups</a></div>
   <div class="btn-updateprofile"><a href="{$WWWROOT}artefact/internal/index.php" alt="Update Profile">Update Profile</a></div>
   <div class="btn-discuss"><a href="{$WWWROOT}group/topics.php" alt="Discuss">Discuss</a></div>
   <div class="btn-uploadfiles"><a href="{$WWWROOT}artefact/file/index.php" alt="Upload Files">Upload Files</a></div>
   <div class="btn-createpages"><a href="{$WWWROOT}view/index.php" alt="Create Pages">Create Pages</a></div>
   <div class="btn-writejournal"><a href="{$WWWROOT}artefact/blog/index.php" alt="Write Journal">Write Journal</a></div>
   <div class="btn-sharepages"><a href="{$WWWROOT}view/share.php" alt="Share Pages">Share Pages</a></div>
{else}
    <div class="btn-logintoexplore">Login to explore!</div>
{/if}
</div>
</div>
{if $USER->is_logged_in()}<p>Click on an activity above</p>
{/if}
<div class="cb"></div>
</div>
