{* Tabs and beginning of page container for group info pages *}
                <ul id="grouppage-tabs">
                {foreach from=$grouptabs key=tab item=tabinfo}
                    <li{if $current == $tab} class="current"{/if}><a href="{$WWWROOT}{$tabinfo.url}">{$tabinfo.title}</a></li>
                {/foreach}
                </ul>
                <div id="grouppage-container">
