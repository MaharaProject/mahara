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
<div class="card view-container{if $suspended} bg-warning{/if}">
    {if $suspended}
    <h2 class="title card-header">{$suspended}</h2>
        {if !$USER->get('admin')}
        <div class="card-body">
            <div class="detail">
                <p>{str tag="unsuspendinstitutiondescription_instadmin" section="admin"}</p>
            </div>
        </div>
        {/if}
    {/if}
    <div class="card-body">
        {$institution_form|safe}
    </div>
</div>
{else}

    <div class="btn-group btn-group-top{if $siteadmin && $countinstitutions == 1} only-button{/if}">
        {if $siteadmin}
        <form class="first form-as-button float-start btn-first" action="" method="post">
            <button class="submit btn btn-secondary" type="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
                <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="addinstitution" section="admin"}</span>
            </button>
        </form>
        {/if}

        {if $countinstitutions > 1}

            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="icon icon-pencil-alt left" role="presentation" aria-hidden="true"></span>
                <span class="icon icon-ellipsis-h icon-xs" role="presentation" aria-hidden="true"></span>
                <span class="btn-title visually-hidden">{str tag="edit"}</span>
            </button>
            <div class="dropdown-menu">
                <li class="dropdown-item">
                    <a href="{$WWWROOT}admin/users/institutionusers.php">{str tag="editmembers" section="admin"}</a>
                </li>
                <li class="dropdown-item">
                    <a href="{$WWWROOT}admin/users/institutionstaff.php">{str tag="editstaff" section="admin"}</a>
                </li>
                <li class="dropdown-item">
                    <a href="{$WWWROOT}admin/users/institutionsupportadmins.php">{str tag="editsupportadmins" section="admin"}</a>
                </li>
                <li class="dropdown-item">
                    <a href="{$WWWROOT}admin/users/institutionadmins.php">{str tag="editadmins" section="admin"}</a>
                </li>
            </div>
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
                <th>{str tag="Maximummembers" section="admin"}</th>
                <th>{str tag="Groups" section="admin"}</th>
                <th>{str tag="Maximumgroups" section="admin"}</th>
                <th>{str tag="Staff" section="admin"}</th>
                <th>{str tag="Supportadmins" section="admin"}</th>
                <th>{str tag="Admins" section="admin"}</th>
                <th></th>
                <th><span class="accessible-hidden visually-hidden">{str tag=edit}</span></th>
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
