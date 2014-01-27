{include file="header.tpl"}

{if $delete_form}

<h3>{str tag="deleteinstitution" section="admin"}</h3>
<p><strong>{$institutionname}</strong></p>
<p>{str tag="deleteinstitutionconfirm" section="admin"}</p>
{$delete_form|safe}
{elseif $institution_form}
    {if $suspended}
    <div class="message">
        <h3 class="title">{$suspended}</h2>
        <div class="detail">
        {if $USER->get('admin')}
            <p>{str tag="unsuspendinstitutiondescription_top" section="admin"}</p>
        {else}
            <p>{str tag="unsuspendinstitutiondescription_top_instadmin" section="admin"}</p>
        {/if}
        </div>
        <div>{$suspendform_top|safe}</div>
    </div>
    {/if}
    {if $add}
    <h3 class="title">{str tag="addinstitution" section="admin"}</h3>
    {/if}
    {if $suspendform}
    <div id="suspendinstitution">
        <h3 class="title">{str tag="suspendinstitution" section=admin}</h3>
        <div class="detail">{$suspendform|safe}</div>
    </div>
    {/if}
{$institution_form|safe}
{else}
{$searchform|safe}
<table id="adminstitutionslist" class="fullwidth">
    <thead>
    <tr>
        <th>{str tag="institution"}</th>
        <th class="center">{str tag="Members" section="admin"}</th>
        <th class="center">{str tag="Maximum" section="admin"}</th>
        <th class="center">{str tag="Staff" section="admin"}</th>
        <th class="center">{str tag="Admins" section="admin"}</th>
        <th></th>
        <th><span class="accessible-hidden">{str tag=edit}</span></th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <td>
        {if $siteadmin}
            <form action="" method="post">
                <input type="submit" class="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
            </form>
        {/if}
        </td>
        <td colspan="6" class="institutionedituserbuttons right">{if $countinstitutions > 1}
            <form action="{$WWWROOT}admin/users/institutionusers.php" method="post">
                <input type="submit" class="submit" name="editmembers" value="{str tag="editmembers" section="admin"}">
            </form>
            <form action="{$WWWROOT}admin/users/institutionstaff.php" method="post">
                <input type="submit" class="submit" name="editstaff" value="{str tag="editstaff" section="admin"}">
            </form>
            <form action="{$WWWROOT}admin/users/institutionadmins.php" method="post">
                <input type="submit" class="submit" name="editadmins" value="{str tag="editadmins" section="admin"}">
            </form>
        {/if}</td>
        <td></td>
    </tr>
    </tfoot>
    <tbody>
        {$results.tablerows|safe}
    </tbody>
</table>
<div class="center">
{$results.pagination|safe}
</div>
{/if}

{include file="footer.tpl"}
