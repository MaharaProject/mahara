{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

            <div class="fr leftrightlink"><span class="editicon"><a href="{$WWWROOT}artefact/internal/">&laquo; {str tag="backtoeditprofile" section="artefact.internal"}</a></span></div>
			<h2>{str section="artefact.internal" tag="profileicons"}</h2>

            {$settingsformtag}
            <table id="profileicons" class="hidden tablerenderer">
                <thead>
                    <th>{str tag="image"}</th>
                    <th>{str tag="title"}</th>
                    <th>{str tag="default"}</th>
                    <th>{str tag="delete"}</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <td></td>
                    <td></td>
                    <td><input id="settings_default" type="submit" class="submit" name="default" value="{str tag="default"}" tabindex="2"></td>
                    <td><input id="settings_delete" type="submit" class="submit" name="delete" value="{str tag="delete"}" tabindex="2"></td>
                </tfoot>
            </table>
            <input type="hidden" name="pieform_settings" value="">
            </form>

            <h3>{str tag="uploadprofileicon" section="artefact.internal"}</h3>
            <p>{str tag="profileiconsiconsizenotice" section="artefact.internal"}</p>

            {$uploadform}

{include file="columnleftend.tpl"}

{include file="footer.tpl"}
