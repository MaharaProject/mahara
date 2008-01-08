{*

  This template displays the 'edit blog post' form

 *}

{include file="header.tpl"}

{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
        <h2>{str section="artefact.blog" tag=$pagetitle}</h2>
        {$textinputform}
        <div id='insertimage'></div>
        <div id='uploader'></div>
        <div id='browsebuttonstuff'>
          <input id='browsebutton' type='button' class='button' value='{str tag=browsemyfiles section=artefact.blog}'>
          {contextualhelp plugintype='artefact' pluginname='blog' section='browsemyfiles'}
        </div>
        <div id='browsemyfiles' style='display: none;'>
          <h3>{str tag=myfiles section='artefact.file'}</h3>
          <table id='filebrowser' class='tablerenderer'>
            <thead><tr>
              <th></th>
              <th>{str section=artefact.file tag=Name}</th>
              <th>{str section=artefact.file tag=Description}</th>
              <th>{str section=artefact.file tag=Size}</th>
              <th>{str section=mahara tag=date}</th>
              <th></th>
            </tr></thead>
            <tbody><tr><td></td></tr></tbody>
          </table>
        </div>
        <h3>{str section=artefact.blog tag=attachedfiles}</h3>
        <table id='attachedfiles' class='tablerenderer'>
          <thead><tr>
            <th></th>
            <th>{str section=artefact.file tag=Name}</th>
            <th>{str section=artefact.file tag=Description}</th>
            <th>{str tag=tags}</th>
            <th></th>
          </tr></thead>
          <tbody><tr><td></td></tr></tbody>
        </table>
        {$draftform}
        <div id='savecancel'>
          <input type='button' class='button' value='{str tag=savepost section=artefact.blog}' onclick="saveblogpost()">
          {contextualhelp plugintype='artefact' pluginname='blog' section='saveblogpost'}
          <input type='button' class='button' value='{str tag=cancel}' onclick="canceledit()">
          {contextualhelp plugintype='artefact' pluginname='blog' section='canceledit'}
        </div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
