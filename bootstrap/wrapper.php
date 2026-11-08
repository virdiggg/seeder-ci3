<!DOCTYPE html>
<html lang="id" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex,nofollow" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="Cache-Control" content="max-age=86400" />
    <title><?= tpl_yield('title', 'Custom Codeigniter 3') ?></title>

    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin-left: 1cm;
            margin-right: 1cm;
            line-height: 1.5;
        }
        table {
            font-size: 16px;
        }
        #header {
            text-align: center;
            /* border: 1px solid black; */
        }
        #body {
            padding-top: 20px;
            min-height: 700px;
            /* border: 1px solid red; */
        }
        #footer {
            text-align: center;
            /* border: 1px solid green; */
        }
        #footer table {
            width: 100%;
        }
        img {
            width: 175px;
        }

        @page {
            size: auto;
        }
        @page :left {
            margin-left: 1cm;
            margin-right: 1cm;
        }
        @page :right {
            margin-left: 1cm;
            margin-right: 1cm;
        }
        @page :first {
            margin-top: 1cm
        }
    </style>
    <?= tpl_stack('styles') ?>
</head>

<body>
    <?= tpl_yield('content') ?>

    <script type="text/javascript">
        const TEST = 'ok';
    </script>
    <?= tpl_stack('scripts') ?>
</body>
</html>