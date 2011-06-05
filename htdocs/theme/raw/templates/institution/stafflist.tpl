{if $data}
<table id="{$listtype}list" class="fullwidth listing">
    <tbody>
    {foreach from=$data item=user}
        <tr class="{cycle values='r0,r1'}">
            {include file="institution/staff.tpl" user=$user page=$page}
        </tr>
    {/foreach}
    </tbody>
</table>
{elseif $columnleft && $columnright}
<div id="{$listtype}_left" class="leftdiv">
    <table id="{$listtype}list" class="fullwidth listing">
        <tbody>
        {foreach from=$columnleft item=leftuser}
            <tr class="{cycle values='r0,r1'}">
                {include file="institution/staff.tpl" user=$leftuser page=$page}
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
<div id="{$listtype}_right" class="rightdiv">
    <table id="{$listtype}list" class="fullwidth listing">
        <tbody>
        {foreach from=$columnright item=rightuser}
            <tr class="{cycle values='r0,r1'}">
                {include file="institution/staff.tpl" user=$rightuser page=$page}
            </tr>
        {/foreach}
        </tbody>
    </table>
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
