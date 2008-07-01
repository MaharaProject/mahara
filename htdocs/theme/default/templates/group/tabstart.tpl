{* Tabs and beginning of page container for group info pages *}
                <ul id="grouppage-tabs">
                    <li{if $current == 'info'} class="current"{/if}><a href="{$WWWROOT}group/view.php?id={$groupid|escape}">About</a></li>
                    <li{if $current == 'members'} class="current"{/if}><a href="{$WWWROOT}group/members.php?id={$groupid|escape}">Members</a></li>
                    <li{if $current == 'views'} class="current"{/if}><a href="{$WWWROOT}view/groupviews.php?group={$groupid|escape}">Views</a></li>
                    <li{if $current == 'files'} class="current"{/if}><a href="{$WWWROOT}artefact/file/groupfiles.php?group={$groupid|escape}">Files</a></li>
                </ul>
                <div id="grouppage-container">
