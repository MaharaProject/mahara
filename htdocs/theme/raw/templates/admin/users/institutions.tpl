{include file="header.tpl"}

{if $suspendform}
    <div class="btn-group btn-group-top">{$suspendform|safe}</div>
    <p class="lead">{str tag="suspendinstitutiondescription" section="admin"}</p>
    <p class="lead">{str tag="unsuspendinstitutiondescription_warning" section="admin"}</p>

{/if}


{if $delete_form}
<div class="card bg-danger view-container">
    <h2 class="card-header">{str tag="deleteinstitution" section="admin"}</h2>
    <div class="card-body">
        <p><strong>{$institutionname}</strong></p>
        <p>{str tag="deleteinstitutionconfirm" section="admin"}</p>
        {$delete_form|safe}
    </div>
</div>
{elseif $institution_form}
<div class="card view-container">
    {if $suspended}
    <h2 class="title card-header bg-warning">{$suspended}</h2>
        {if !$USER->get('admin')}
        <div class="card-body">
            <div class="detail">
                <p>{str tag="unsuspendinstitutiondescription_instadmin" section="admin"}</p>
            </div>
        </div>
        {/if}
    {/if}

    {if $add}
    <h2 class="title card-header">{str tag="addinstitution" section="admin"}</h2>
    {/if}
    <div class="card-body">
        {$institution_form|safe}
    </div>
</div>
{else}

    <div class="btn-group btn-group-top{if $siteadmin && $countinstitutions == 1} only-button{/if}">
        {if $siteadmin}
        <form class="form-as-button float-left btn-first" action="" method="post">
            <button class="submit btn btn-secondary" type="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
                <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="addinstitution" section="admin"}</span>
            </button>
        </form>
        {/if}

        {if $countinstitutions > 1}

            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="icon icon-pencil icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="icon icon-ellipsis-h icon-xs" role="presentation" aria-hidden="true"></span>
                <span class="btn-title sr-only">{str tag="edit"}</span>
            </button>


            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li class="">
                    <form class="form-as-button float-left" action="{$WWWROOT}admin/users/institutionusers.php" method="post">
                        <button class="submit btn btn-link" type="submit" name="editmembers" value="{str tag="editmembers" section="admin"}">
                            {str tag="editmembers" section="admin"}
                        </button>
                    </form>
                </li>
                <li class="">
                    <form class="form-as-button float-left" action="{$WWWROOT}admin/users/institutionstaff.php" method="post">
                        <button class="submit btn btn-link" type="submit" name="editstaff" value="{str tag="editstaff" section="admin"}">
                            {str tag="editstaff" section="admin"}
                        </button>
                    </form>
                </li>
                <li class="">
                    <form class="form-as-button float-left" action="{$WWWROOT}admin/users/institutionadmins.php" method="post">
                          <button class="submit btn btn-link" type="submit" name="editadmins" value="{str tag="editadmins" section="admin"}">
                            {str tag="editadmins" section="admin"}
                        </button>
                    </form>
                </li>
            </ul>
        {/if}
    </div>
 {$searchform|safe}

<div class="card view-container">
    <div class="table-responsive">
        <table id="adminstitutionslist" class="fullwidth table table-striped">
            <thead>
            <tr>
                <th>{str tag="institution"}</th>
                <th>{str tag="Shortname" section="admin"}</th>
                <th>{str tag="Members" section="admin"}</th>
                <th>{str tag="Maximum" section="admin"}</th>
                <th>{str tag="Staff" section="admin"}</th>
                <th>{str tag="Admins" section="admin"}</th>
                <th></th>
                <th><span class="accessible-hidden sr-only">{str tag=edit}</span></th>
            </tr>
            </thead>
            <tbody>
                {$results.tablerows|safe}
            </tbody>
        </table>
    </div>
</div>


<div class="center">
{$results.pagination|safe}
</div>
{/if}

{include file="footer.tpl"}
