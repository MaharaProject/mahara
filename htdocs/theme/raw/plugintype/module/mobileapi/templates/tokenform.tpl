<!doctype html>
<head>
    <meta name="generator" content="Mahara {$SERIES} (https://mahara.org)" />
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script>
        window.onload = function() {
            console.log("Running: {$token}");
            window.maharatoken = "{$token}";
        }
    </script>
{foreach from=$STYLESHEETLIST item=cssurl}
    <link rel="stylesheet" type="text/css" href="{$cssurl}">
{/foreach}
</head>
<body>
<!-- TODO: Put some nice "Redirecting you..." placeholder here? -->
<span class="icon icon-spinner icon-pulse"></span>
</body>