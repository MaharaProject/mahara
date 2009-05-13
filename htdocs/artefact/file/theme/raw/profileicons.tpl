{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
            {$settingsformtag}
            <table id="profileicons" class="hidden tablerenderer">
                <thead>
                    <th>{str tag="image"}</th>
                    <th>{str tag="imagetitle" section=artefact.file}</th>
                    <th>{str tag="Default" section=artefact.file}</th>
                    <th>{str tag="delete"}</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <td colspan="4" class="right"><input id="settings_default" type="submit" class="submit" name="default" value="{str tag="setdefault" section=artefact.file}" tabindex="2"> {str tag="or" section="artefact.file"} <input type="submit" class="submit" name="unsetdefault" value="{str tag="usenodefault" section="artefact.file}" tabindex="2"> <input id="settings_delete" type="submit" class="cancel" name="delete" value="{str tag="deleteselectedicons" section=artefact.file}" tabindex="2"></td>
                </tfoot>
            </table>
            <input type="hidden" name="pieform_settings" value="">
            </form>

            <h3>{str tag="uploadprofileicon" section="artefact.file"}</h3>
            <p>{str tag="profileiconsiconsizenotice" section="artefact.file" args=$imagemaxdimensions}</p>

            {$uploadform}

{include file="columnleftend.tpl"}

{include file="footer.tpl"}
