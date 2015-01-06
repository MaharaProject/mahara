{if $data}
<div id="{$listtype}list" class="fullwidth listing">
    {foreach from=$data item=user}
        <div class="{cycle values='r0,r1'}">
            {include file="institution/staff.tpl" user=$user page=$page}
        </div>
    {/foreach}
</div>
{elseif $columnleft && $columnright}
<div id="{$listtype}_left" class="leftdiv">
    <div id="{$listtype}list" class="fullwidth listing">
        {foreach from=$columnleft item=leftuser}
            <div class="{cycle values='r0,r1'}">
                {include file="institution/staff.tpl" user=$leftuser page=$page}
            </div>
        {/foreach}
    </div>
</div>
<div id="{$listtype}_right" class="rightdiv">
    <div id="{$listtype}list" class="fullwidth listing">
        {foreach from=$columnright item=rightuser}
            <div class="{cycle values='r1,r0'}">
                {include file="institution/staff.tpl" user=$rightuser page=$page}
            </div>
        {/foreach}
    </div>
</div>
{else}
    <div class="message">
    {if $listtype == 'staff'}
        {str tag=noinstitutionstafffound section=mahara}
    {elseif $listtype == 'admin'}
        {str tag=noinstitutionadminfound section=mahara}
    {/if}
    </div>
{/if}
