{if $sbdata.data || $sbdata.preview || $sbdata.count > 1}
    <div class="sidebar-header">
        <h3>{if $sbdata.preview}{str tag="profilecompletenesspreview"}{else}{str tag="profilecompleteness"}{/if}</h3>
        {if $sbdata.count > 1}
        <script language="javascript">
        function reloadSideBar() {
            window.location.href = window.location.href.split('?')[0]+'?i='+$('progresssidebarselect_institution').value;
        }
        addLoadEvent(function() {
            connect($('progresssidebarselect_institution'), 'onchange', reloadSideBar);
        });
        </script>
        <form class="pieform" name="progresssidebarselect" method="post" action="" id="progresssidebarselect">
        <table cellspacing="0"><tbody>
            <tr id="progresssidebarselect_institution_container" class="select">
                <th><label for="progresssidebarselect_institution">{str tag=profilecompletionforwhichinstitution}</label></th>
                <td><select class="select autofocus" id="progresssidebarselect_institution" name="institution" tabindex="1" style="">
                {foreach from=$sbdata.institutions key=inst item=displayname}
                    <option value="{$inst}"{if $inst == $sbdata.institution} selected="selected"{/if}>{$displayname|str_shorten_html:25:true}</option>
                {/foreach}
                </select></td>
            </tr>
        </tbody></table>
        </form>
        {/if}
    </div>
{/if}
{if $sbdata.data || $sbdata.preview}
    <div class="sidebar-content">
        <div id="progressbarwrap">
        {if $sbdata.percent < 100}
            <div id="progress_bar_fill" style="width: {$sbdata.percent*2}px;">&nbsp;</div>
            <p id="progress_bar">
                <span id="progress_bar_percentage">{$sbdata.percent}%</span>
            </p>
            <div id="profile_completeness_tips">
                <span class="hidden" id="progress_counting_total">{$sbdata.totalcounting}</span>
                <span class="hidden" id="progress_completed_total">{$sbdata.totalcompleted}</span>
                <strong>{str tag=profilecompletenesstips}</strong>
                <ul>
                {foreach from=$sbdata.data item=item}
                <li{if $item.display <= 0} class="hidden"{/if}>
                    <span id="progress_counting_{$item.artefact}" class="hidden">{$item.counting}</span>
                    <span id="progress_completed_{$item.artefact}" class="hidden">{$item.completed}</span>
                    {if $item.link}<a href="{$WWWROOT}{$item.link}">{/if}<span id="progress_item_{$item.artefact}">{$item.label}</span>{if $item.link}</a>{/if}
                </li>
                {/foreach}
                </ul>
            </div>
        {else}
            <div id="progress_bar_fill" style="display: none; width: {$sbdata.percent*2}px;">&nbsp;</div>
            <p id="progress_bar_100">
                <span id="progress_bar_percentage">{$sbdata.percent}%</span>
            </p>
        {/if}
        </div>
    </div>
{else}
    {if $sbdata.totalcounting == 0 && $sbdata.count > 1}
    <div class="sidebar-content">{str tag="noprogressitems"}</div>
    {/if}
{/if}
