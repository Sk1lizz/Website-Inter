<?php
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "
SELECT sp.id, sp.price, sp.variant_size, sp.variant_height, sp.status, sp.purchased_at,
       p.name AS product, pl.name AS player
FROM shop_purchases sp
JOIN shop_products p ON p.id = sp.product_id
JOIN players pl ON pl.id = sp.player_id
ORDER BY sp.purchased_at DESC
";
$res = $db->query($sql);

$out = [];
while($r = $res->fetch_assoc()){
  $out[] = $r;
}
echo json_encode($out);
