<!DOCTYPE html>
<html>
<head>
    <title><?php the_title() ?></title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }

        .affiliate-links-wrap {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            top: 0;
        }

        iframe.affiliate-links-iframe {
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
        }
    </style>
</head>
<body>
<div class="affiliate-links-wrap">
    <iframe class="affiliate-links-iframe" width="100%" height="100%"
            frameborder="0" src="<?php echo $target_url ?>"></iframe>
</div>
</body>
</html>