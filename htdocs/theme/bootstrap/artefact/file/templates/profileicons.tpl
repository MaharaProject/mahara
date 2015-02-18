{include file="header.tpl"}
    {$settingsformtag|safe}
    <div class="table-responsive">
        <table id="profileicons" class="hidden tablerenderer fullwidth table table-striped">
            <thead>
                <tr>
                    <th class="profileiconcell">{str tag="image"}</th>
                    <th>{str tag="imagetitle" section=artefact.file}</th>
                    <th class="text-center">{str tag="Default" section=artefact.file}</th>
                    <th class="text-center">{str tag="delete"}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">
                            <span class="fa fa-ok prs"></span>
                            {str tag="setdefault" section=artefact.file}
                        </button>
                        <button id="settings_delete" type="submit" class="delete btn btn-danger" name="delete">
                             <span class="fa fa-trash prs"></span> 
                             {str tag="deleteselectedicons" section=artefact.file}
                         </button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <input type="hidden" name="pieform_settings" value="">
    <input type="hidden" name="sesskey" value="{$USER->get('sesskey')}">
    </form>

    <h3>{str tag="uploadprofileicon" section="artefact.file"}</h3>
    <p>{str tag="profileiconsiconsizenotice" section="artefact.file" args=$imagemaxdimensions}</p>

    {$uploadform|safe}
{include file="footer.tpl"}
