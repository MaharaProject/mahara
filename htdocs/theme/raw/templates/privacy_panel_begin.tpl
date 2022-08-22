{if $institutionprivacy}
<div id="instprivacy" class="inst js-hidden card">
{elseif $institutionterms}
<div id ="insttermsandconditions" class ="inst js-hidden card">
{else}
<div class="card">
{/if}
    <div class="first last form-group collapsible-group">
        <fieldset class="pieform-fieldset last collapsible">
            <legend>
                <a
                {if $institutionprivacy}
                    href="#dropdowninstprivacy"
                {elseif $institutionterms}
                    href="#dropdowninstterms"
                {else}
                    href="#dropdown{$privacy->id}"
                {/if}
                data-bs-toggle="collapse" aria-expanded="false" aria-controls="dropdown">
                    {$privacytitle}
                    <span class="icon icon-chevron-down collapse-indicator right float-end"></span>
                </a>
            </legend>
            <div class="fieldset-body collapse {if (!($privacy->agreed && $ignoreagreevalue) || $ignoreformswitch)}show{/if}"
              {if $institutionprivacy}
                  id="dropdowninstprivacy"
              {elseif $institutionterms}
                  id="dropdowninstterms"
              {else}
                  id="dropdown{$privacy->id}"
              {/if}>

                {if $privacytime}
                    <span class="text-midtone text-small">{str tag='lastupdated' section='admin'} {$privacytime} </span>
                {/if}
                {if $institutionprivacy}
                    <div id ="instprivacytext" class="insttext"></div>
                {elseif $institutionterms}
                    <div id ="insttermsandconditionstext" class="insttext"></div>
                {else}
                    {$privacy->content|safe}
                {/if}
            </div>
            <div class="fieldset-body consentbutton collapse {if (!($privacy->agreed && $ignoreagreevalue) || $ignoreformswitch)}show{/if}">
