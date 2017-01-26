{include file="header.tpl"}

{if $suspendform}
    <div class="btn-group btn-group-top">{$suspendform|safe}</div>
    <p class="lead">{str tag="suspendinstitutiondescription" section="admin"}</p>
{/if}


{if $delete_form}
<div class="panel panel-danger view-container">
    <h2 class="panel-heading">{str tag="deleteinstitution" section="admin"}</h2>
    <div class="panel-body">
        <p><strong>{$institutionname}</strong></p>
        <p>{str tag="deleteinstitutionconfirm" section="admin"}</p>
        {$delete_form|safe}
    </div>
</div>
{elseif $institution_form}
<div class="panel panel-default view-container">
    {if $suspended}
        <h2 class="title panel-heading">{$suspended}</h2>
        <div class="panel-body">
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
    <h2 class="title panel-heading">{str tag="addinstitution" section="admin"}</h2>
    {/if}
    <div class="panel-body">
        {$institution_form|safe}
    </div>
</div>
{else}

    <div class="btn-group btn-group-top{if $siteadmin && $countinstitutions == 1} only-button{/if}">
        {if $siteadmin}
        <form class="form-as-button pull-left" action="" method="post">
            <button class="submit btn btn-default" type="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
                <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="addinstitution" section="admin"}</span>
            </button>
        </form>
        {/if}

        {if $countinstitutions > 1}

            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="icon icon-pencil icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="icon icon-ellipsis-h icon-xs" role="presentation" aria-hidden="true"></span>
                <span class="btn-title sr-only">{str tag="edit"}</span>
            </button>


            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li>
                    <form class="form-as-button pull-left" action="{$WWWROOT}admin/users/institutionusers.php" method="post">
                        <button class="submit btn btn-link" type="submit" name="editmembers" value="{str tag="editmembers" section="admin"}">
                            {str tag="editmembers" section="admin"}
                        </button>
                    </form>
                </li>
                <li>
                    <form class="form-as-button pull-left" action="{$WWWROOT}admin/users/institutionstaff.php" method="post">
                        <button class="submit btn btn-link" type="submit" name="editstaff" value="{str tag="editstaff" section="admin"}">
                            {str tag="editstaff" section="admin"}
                        </button>
                    </form>
                </li>
                <li>
                    <form class="form-as-button pull-left" action="{$WWWROOT}admin/users/institutionadmins.php" method="post">
                          <button class="submit btn btn-link" type="submit" name="editadmins" value="{str tag="editadmins" section="admin"}">
                            {str tag="editadmins" section="admin"}
                        </button>
                    </form>
                </li>
            </ul>
        {/if}
    </div>
 {$searchform|safe}

<div class="panel panel-default view-container">
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
