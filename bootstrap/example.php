<?php tpl_extend('layouts/wrapper') ?>

<?php tpl_section('title', 'Example') ?>

<?php tpl_push('styles') ?>
    <style>
        tbody {
            background-color:blue;
        }
    </style>
<?php tpl_end() ?>

<?php tpl_section('content') ?>
    <section id="breadcrumb">
        <h1>Example</h1>
        <p>Hello, <?= html_escape($username) ?> (<?= $email ?>)</p>
    </section>

    <section id="header">
        <h2><u>CURRICULUM VITAE</u></h2>
    </section>

    <section id="body">
        <table border="0">
            <thead>
                <tr>
                    <td>Nama</td>
                    <td>: <?= $username ?></td>
                </tr>
                <tr>
                    <td>Tempat&TglLahir</td>
                    <td>: <?= $username ?></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: <?= $username ?></td>
                </tr>
                <tr>
                    <td>No. Telp</td>
                    <td>: <?= $username ?></td>
                </tr>
                <tr>
                    <td>Pendidikan</td>
                    <td>: <?= $email ?></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">&#9866;&nbsp;<?= $email ?></td>
                </tr>
            </tbody>
        </table>
    </section>
    <section id="footer">
        <table border="0">
            <tbody>
                <tr>
                    <td style="width:55%"></td>
                    <td style="width:45%">Tangerang, <?= date('Y-m-d') ?><br>Yang Membuat,</td>
                </tr>
                <tr>
                    <td></td>
                    <td><img src="<?= assets('no-image.jpg') ?>" /></td>
                </tr>
                <tr>
                    <td></td>
                    <td><u><?= $username ?></u><br>(Penanggung Jawab Teknis)</td>
                </tr>
            </tbody>
        </table>
    </section>
<?php tpl_end() ?>

<?php tpl_push('scripts') ?>
    <script>console.log('dashboard only');</script>
    <script>console.log(TEST);</script>
<?php tpl_end() ?>