{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

			<h2>{str section="artefact.internal" tag="profileicons"}</h2>

            {$settingsformtag}
            <table id="profileicons" class="hidden tablerenderer">
                <thead>
                    <th>{str tag="image"}</th>
                    <th>{str tag="Title" section=artefact.internal}</th>
                    <th>{str tag="Default" section=artefact.internal}</th>
                    <th>{str tag="delete"}</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <td></td>
                    <td></td>
                    <td><input id="settings_default" type="submit" class="submit" name="default" value="{str tag="setdefault" section=artefact.internal}" tabindex="2"> {str tag="or" section="artefact.internal"} <input type="submit" class="submit" name="unsetdefault" value="{str tag="usenodefault" section="artefact.internal}" tabindex="2"></td>
                    <td><input id="settings_delete" type="submit" class="submit" name="delete" value="{str tag="deleteselectedicons" section=artefact.internal}" tabindex="2"></td>
                </tfoot>
            </table>
            <input type="hidden" name="pieform_settings" value="">
            </form>

            <h3>{str tag="uploadprofileicon" section="artefact.internal"}</h3>
            <p>{str tag="profileiconsiconsizenotice" section="artefact.internal" args=$imagemaxdimensions}</p>

            {$uploadform}

{include file="columnleftend.tpl"}

{include file="footer.tpl"}
