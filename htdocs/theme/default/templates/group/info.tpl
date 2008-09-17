                <ul id="group-info">
                    <li><strong>{str tag=groupadmins section=group}:</strong> {foreach name=admins from=$group->admins item=id}
                    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$id|escape}" alt="">
                    <a href="{$WWWROOT}user/view.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$smarty.foreach.admins.last}, {/if}
                    {/foreach}</li>
                    <li><strong>{str tag=Created section=group}:</strong> {$group->ctime}</li>
                    <li><strong>{str tag=Members section=group}:</strong> {$membercount}&nbsp;
                        <strong>{str tag=Views section=view}:</strong> {$viewcount}&nbsp;
                        <strong>{str tag=Files section=artefact.file}:</strong> {$filecount}&nbsp;
                        <strong>{str tag=Folders section=artefact.file}:</strong> {$foldercount}</li>
                </ul>
