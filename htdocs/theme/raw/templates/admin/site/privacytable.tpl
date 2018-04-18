        <tr>
            <td>{$result->version}</td>
            <td>{if $result->userid == '0' || $result->userdeleted == '1'}
                    {$result->displayname}
                {else}
                    <a href="{$WWWROOT}user/view.php?id={$result->userid}">
                        {$result->displayname}
                    </a>
                {/if}
            </td>
            <td>{$result->content|htmlspecialchars_decode|strip_tags|truncate:100:"..."}</td>
            <td>{$result->ctime|date_format:'%d %b %Y %H:%M'}</td>
            <td class="control-buttons">
                {if $result->id == $result->current}
                    <div class="btn-group">
                        <a href="{$WWWROOT}{$link}{$result->id}" title="{str tag=editversion section='admin' arg1='$result->version'}" class="btn btn-secondary btn-sm">
                            <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                        </a>
                    </div>
                {else}
                    <div class="btn-group">
                        <a href="{$WWWROOT}{$link}{$result->id}" title="{str tag=viewversion section='admin' arg1='$result->version'}" class="btn btn-secondary btn-sm">
                            <span class="icon icon-eye icon-lg" role="presentation" aria-hidden="true"></span>
                        </a>
                    </div>
                {/if}
            </td>
        </tr>
        <!-- Because T&C and PS can have the same version, we must check the id as well -->
        {if $result->id == $versionid}
        <tr>
            <td colspan="5">
                <div>
                {if $result->type == 'privacy'}
                    {str tag=privacyversionfor section=admin arg1="$result->version"}
                {else}
                    {str tag=termsversionfor section=admin arg1="$result->version"}
                {/if}
                </div>
                {$result->content|clean_html|safe}
            </td>
        </tr>
        {/if}