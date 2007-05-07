    <h3>{str tag="selfsearch"}{contextualhelp plugintype='core' pluginname='core' section='selfsearch'}</h3>
    <form id="selfsearch" method="post" action="{$WWWROOT}selfsearch.php">
        <input type="text" name="query">
        <button type="submit" class="button">{str tag="go"}</button>
    </form>
