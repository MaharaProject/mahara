{if $data}
<div id="{$listtype}list" class="fullwidth list-group list-group-lite">
    {foreach from=$data item=user}
        {include file="institution/staff.tpl" user=$user page=$page mrmoduleactive=$mrmoduleactive}
    {/foreach}
</div>
{elseif $columnleft && $columnright}
<div id="{$listtype}_left" class="leftdiv">
    <div id="{$listtype}list" class="fullwidth list-group">
        {foreach from=$columnleft item=leftuser}
            <div class="list-group-item">
                {include file="institution/staff.tpl" user=$leftuser page=$page mrmoduleactive=$mrmoduleactive}
            </div>
        {/foreach}
    </div>
</div>
<div id="{$listtype}_right" class="rightdiv">
    <div id="{$listtype}list" class="fullwidth list-group">
        {foreach from=$columnright item=rightuser}
            <div class="list-group-item">
                {include file="institution/staff.tpl" user=$rightuser page=$page mrmoduleactive=$mrmoduleactive}
            </div>
        {/foreach}
    </div>
</div>
{else}
    <div class="no-results">
    {if $listtype == 'staff'}
        {str tag=noinstitutionstafffound section=mahara}
    {elseif $listtype == 'admin'}
        {str tag=noinstitutionadminfound section=mahara}
    {/if}
    </div>
{/if}
