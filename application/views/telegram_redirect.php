<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Redirect</title>
    <script>
        const queryString = window.location.search
        const urlParams = new URLSearchParams(queryString)
        document.cookie = 'macca_redirect=' + urlParams.get('url')
        window.location.href = 'https://helpdesk.kallagroup.co.id/acp'
    </script>
</head>
<body>

</body>
</html>