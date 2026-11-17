    </main>
</div>
<?php $f = flash(); ?>
<div id="flash" data-tipe="<?= e($f['tipe'] ?? '') ?>" data-pesan="<?= e($f['pesan'] ?? '') ?>" hidden></div>
<script src="<?= url('assets/vendor/jquery.min.js') ?>"></script>
<script src="<?= url('assets/vendor/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= url('assets/vendor/dataTables.min.js') ?>"></script>
<script src="<?= url('assets/vendor/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= url('assets/vendor/sweetalert2.all.min.js') ?>"></script>
<script src="<?= aset('assets/js/app.js') ?>"></script>
<?php foreach ($js_tambahan ?? [] as $js): ?>
<script src="<?= aset($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
