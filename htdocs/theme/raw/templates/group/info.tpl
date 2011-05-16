                <ul>
                    <li><label class="groupinfolabel">{str tag=groupadmins section=group}:</label> {foreach name=admins from=$group->admins item=id}
                    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$id}" alt="">
                    <a href="{$WWWROOT}user/view.php?id={$id}">{$id|display_name}</a>{if !$.foreach.admins.last}, {/if}
                    {/foreach}</li>
                    <li><label class="groupinfolabel">{str tag=grouptype section=group}:</label> {$group->settingsdescription}</li>
                    {if $group->categorytitle}<li><label>{str tag=groupcategory section=group}:</label> {$group->categorytitle}</li>{/if}
                    <li><label class="groupinfolabel">{str tag=Created section=group}:</label> {$group->ctime}</li>
                    <li class="last"><span><label>{str tag=Members section=group}:</label> {$membercount}&nbsp;</span>
                        <span><label>{str tag=Views section=view}:</label> {$viewcount}&nbsp;</span>
                        <span><label>{str tag=Files section=artefact.file}:</label> {$filecount}&nbsp;</span>
                        <span><label>{str tag=Folders section=artefact.file}:</label> {$foldercount}</span></li>
                </ul>
