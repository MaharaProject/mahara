{if $institutionprivacy}
<div id="instprivacy" class="inst js-hidden card">
{elseif $institutionterms}
<div id ="insttermsandconditions" class ="inst js-hidden card">
{else}
<div class="card">
{/if}
    <div class="last form-group collapsible-group">
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
                data-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                    {$privacytitle}
                    <span class="icon icon-chevron-down collapse-indicator right float-right"></span>
                </a>
            </legend>
            <div class="fieldset-body collapse {if (!($privacy->agreed && $ignoreagreevalue) || $ignoreformswitch)}show{/if}"
              {if $institutionprivacy}
                  id="dropdowninstprivacy">
              {elseif $institutionterms}
                  id="dropdowninstterms">
              {else}
                  id="dropdown{$privacy->id}">
              {/if}
                {if $institutionprivacy}
                    <div id ="instprivacytext" class="insttext"></div>
                {elseif $institutionterms}
                    <div id ="insttermsandconditionstext" class="insttext"></div>
                {else}
                    {$privacy->content|safe}
                {/if}
                  {if $privacytime}
                      <span class="text-midtone text-small">{str tag='lastupdated' section='admin'} {$privacytime} </span>
                  {/if}
            </div>
            <div class="fieldset-body consentbutton collapse {if (!($privacy->agreed && $ignoreagreevalue) || $ignoreformswitch)}show{/if}">
