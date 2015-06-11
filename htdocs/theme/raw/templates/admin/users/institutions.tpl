{include file="header.tpl"}

{if $delete_form}

    <h3>{str tag="deleteinstitution" section="admin"}</h3>
    <p><strong>{$institutionname}</strong></p>
    <p>{str tag="deleteinstitutionconfirm" section="admin"}</p>
    {$delete_form|safe}
{elseif $institution_form}
    {if $suspended}
    <div class="">
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

    <div class="btn-group btn-group-top">
        {if $siteadmin}
        <form class="form-as-button pull-left" action="" method="post">
            <button class="submit btn btn-default" type="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
                <span class="icon icon-plus icon-lg text-success prs"></span>
                <span class="hidden-xs">{str tag="addinstitution" section="admin"}</span>
            </button>
        </form>
        {/if}
           
        {if $countinstitutions > 1}


            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="icon icon-pencil icon-lg prs"></span>
                <span class="icon icon-ellipsis-h icon-xs"></span>
                <span class="sr-only">{str tag="edit"}</span>
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

<div class="panel panel-default panel-body mtl">
   
    <div class="table-responsive">
        <table id="adminstitutionslist" class="fullwidth table table-striped">
            <thead>
            <tr>
                <th>{str tag="institution"}</th>
                <th class="center">{str tag="Members" section="admin"}</th>
                <th class="center">{str tag="Maximum" section="admin"}</th>
                <th class="center">{str tag="Staff" section="admin"}</th>
                <th class="center">{str tag="Admins" section="admin"}</th>
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
