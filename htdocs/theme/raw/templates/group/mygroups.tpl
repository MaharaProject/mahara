{auto_escape off}
{include file="header.tpl"}
{if $cancreate}
            <div class="rbuttons">
                <a href="{$WWWROOT}group/create.php" class="btn">{str tag="creategroup" section="group"}</a>
            </div>
{/if}
{$form}
{if $groups}
{foreach from=$groups item=group}
            <div class="{cycle values='r0,r1'} listing">
                <div class="fr">
                     {include file="group/groupuserstatus.tpl" group=$group returnto='find'}
                </div>
                <div>
                     {include file="group/group.tpl" group=$group returnto='mygroups'}
                </div>
            </div>
{/foreach}
{$pagination}
{else}
            <div class="message">{str tag="trysearchingforgroups" section="group" args=$searchingforgroups}</div>
{/if}
{include file="footer.tpl"}
{/auto_escape}
