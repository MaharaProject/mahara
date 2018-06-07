<tr id="buttonsrow">
    <td colspan="{math equation="x-2" x=count($cols)}">
        <div id="nousersselected" class="d-none error">{str tag=nousersselected section=admin}</div>
    </td>
    <td>
        <form class="nojs-hidden-inline" id="archive" action="{$WWWROOT}admin/users/exportqueue.php" method="post">
            <label class="accessible-hidden sr-only" for="exportbtn">{str tag=withselectedcontentexport section=admin}</label>
            <input type="button" class="button btn btn-secondary btn-sm" name="export" id="exportbtn" value="{str tag=requeue section=export}">
        </form>
    </td>
    <td>
        <form class="nojs-hidden-inline" id="exportdelete" action="{$WWWROOT}admin/users/exportqueue.php" method="post">
            <label class="accessible-hidden sr-only" for="deletebtn">{str tag=withselectedcontentdelete section=admin}</label>
            <input type="button" class="button btn btn-secondary btn-sm" name="delete" id="deletebtn" value="{str tag=delete section=mahara}">
        </form>
    </td>
</tr>
