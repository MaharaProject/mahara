                <ul>
                    <li>{$group->settingsdescription}</li>
                    <li><label class="groupinfolabel">{str tag=groupadmins section=group}:</label> {foreach name=admins from=$group->admins item=user}
                    <img src="{profile_icon_url user=$user maxwidth=20 maxheight=20}" alt="">
                    <a href="{profile_url($user)}">{$user|display_name}</a>{if !$.foreach.admins.last}, {/if}
                    {/foreach}</li>
                    {if $group->categorytitle}<li><label>{str tag=groupcategory section=group}:</label> {$group->categorytitle}</li>{/if}
                    <li><label class="groupinfolabel">{str tag=Created section=group}:</label> {$group->ctime}</li>
                    {if $editwindow}<li><label class="groupinfolabel">{str tag=editable section=group}:</label> {$editwindow}</li>{/if}
                    <li class="last">
                        {if $group->membercount}<span><label>{str tag=Members section=group}:</label>&nbsp;{$group->membercount}</span>{/if}
                        <span><label>{str tag=Views section=view}:</label>&nbsp;{$group->viewcount}</span>
                        <span><label>{str tag=Files section=artefact.file}:</label>&nbsp;{$group->filecounts->files}</span>
                        <span><label>{str tag=Folders section=artefact.file}:</label>&nbsp;{$group->filecounts->folders}</span>
                        <span><label>{str tag=nameplural section=interaction.forum}:</label>&nbsp;{$group->forumcounts}</span>
                        <span><label>{str tag=Topics section=interaction.forum}:</label>&nbsp;{$group->topiccounts}</span>
                        <span><label>{str tag=Posts section=interaction.forum}:</label>&nbsp;{$group->postcounts}</span>
                    </li>
                </ul>
