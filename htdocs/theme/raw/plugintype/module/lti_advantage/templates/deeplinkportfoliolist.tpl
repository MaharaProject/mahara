<html>
  <head>
    <title>{$title|safe}</title>
    {foreach from=$STYLESHEETLIST item=cssurl}
        <link rel="stylesheet" type="text/css" href="{$cssurl}">
    {/foreach}
  </head>
  <body class="lti-advantage-deeplinks">
  <div id="ltimodalwrapper" class="ltimodalwrapper ltimodal">
    <div class="ltimodal-box">
      <div class="ltimodal-title"><h2>{str tag='confirmareyousure' section='module.lti_advantage'}</h2></div>
      <div class="ltimodal-content">
        <!-- Modal content -->
        <p>{str tag='confirmwarning1' section='module.lti_advantage' arg1='<span class="title" id="submissionTitle"></span>'}</p>
      </div>
      <div class="btns">
        <a id="submissionLink" class="btn btn-primary" href="">{str tag='confirmbtntxtconfirm' section='module.lti_advantage'}</a>
        <a onclick="closeModal();" class="btn btn-secondary">{str tag='confirmbtntxtcancel' section='module.lti_advantage'}</a>
      </div>
    </div>
  </div>
  {if $links}
    {foreach $links section}
    <h2>{$section.title|safe}</h2>
    <ul>
      {foreach $section.links link}
        <li onclick="confirmSubmission('{$link.href}', '{$link.text|safe|escape:'quotes'}');">{$link.text|safe}</li>
      {/foreach}
    </ul>
    {/foreach}
    <script>
      // Get the modal
      var modal = document.getElementById("ltimodalwrapper");

      function confirmSubmission(link, title) {
        // Set the link and title, then display the modal.
        document.getElementById("submissionTitle").textContent = title;
        document.getElementById("submissionLink").setAttribute("href", link)
        modal.style.display = "block";
      }

      // Close the modal.
      function closeModal() {
        modal.style.display = "none";
      }

      // When the user clicks anywhere outside of the modal, close it
      window.onclick = function(event) {
        if (event.target == modal) {
          closeModal();
        }
      }
    </script>
  {else}
    {$nolinks|safe}
  {/if}
  </body>
</html>