                <ul>
                    <li>{$group->settingsdescription}</li>
                    <li><strong class="groupinfolabel">{str tag=groupadmins section=group}:</strong> {foreach name=admins from=$group->admins item=user}
                    <img src="{profile_icon_url user=$user maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
                    <a href="{profile_url($user)}">{$user|display_name}</a>{if !$.foreach.admins.last}, {/if}
                    {/foreach}</li>
                    {if $group->categorytitle}<li><strong>{str tag=groupcategory section=group}:</strong> {$group->categorytitle}</li>{/if}
                    <li><strong class="groupinfolabel">{str tag=Created section=group}:</strong> {$group->ctime}</li>
                    {if $editwindow}<li><strong class="groupinfolabel">{str tag=editable section=group}:</strong> {$editwindow}</li>{/if}
                    <li class="last">
                        {if $group->membercount}<span><strong>{str tag=Members section=group}:</strong>&nbsp;{$group->membercount}</span>{/if}
                        <span><strong>{str tag=Views section=view}:</strong>&nbsp;{$group->viewcount}</span>
                        <span><strong>{str tag=Files section=artefact.file}:</strong>&nbsp;{$group->filecounts->files}</span>
                        <span><strong>{str tag=Folders section=artefact.file}:</strong>&nbsp;{$group->filecounts->folders}</span>
                        <span><strong>{str tag=nameplural section=interaction.forum}:</strong>&nbsp;{$group->forumcounts}</span>
                        <span><strong>{str tag=Topics section=interaction.forum}:</strong>&nbsp;{$group->topiccounts}</span>
                        <span><strong>{str tag=Posts section=interaction.forum}:</strong>&nbsp;{$group->postcounts}</span>
                    </li>
                </ul>
