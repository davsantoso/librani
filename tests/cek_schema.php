<?php
reset_db();
$tabel = db()->query("SELECT table_name AS n, engine AS e, table_collation AS c FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchAll();
sama('jumlah tabel', 14, count($tabel));
foreach ($tabel as $t) {
    sama("engine {$t['n']}", 'InnoDB', $t['e']);
    sama("collation {$t['n']}", 'utf8mb4_unicode_ci', $t['c']);
}
sama('pengaturan default denda_per_hari', '1000', db()->query("SELECT nilai FROM pengaturan WHERE kunci='denda_per_hari'")->fetchColumn());
sama('admin awal ada', 1, (int) db()->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn());
$fk = (int) db()->query("SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema = DATABASE()")->fetchColumn();
cek("foreign key terpasang (>= 17, ada $fk)", $fk >= 17);
