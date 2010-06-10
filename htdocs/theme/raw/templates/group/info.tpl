{auto_escape off}
                <ul>
                    <li><label>{str tag=groupadmins section=group}:</label> {foreach name=admins from=$group->admins item=id}
                    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$id|escape}" alt="">
                    <a href="{$WWWROOT}user/view.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$.foreach.admins.last}, {/if}
                    {/foreach}</li>
                    <li>{$group->settingsdescription}</li>
                    <li><label>{str tag=Created section=group}:</label> {$group->ctime}</li>
                    <li><span><label>{str tag=Members section=group}:</label> {$membercount}&nbsp;</span>
                        <span><label>{str tag=Views section=view}:</label> {$viewcount}&nbsp;</span>
                        <span><label>{str tag=Files section=artefact.file}:</label> {$filecount}&nbsp;</span>
                        <span><label>{str tag=Folders section=artefact.file}:</label> {$foldercount}</span></li>
                </ul>
{/auto_escape}
