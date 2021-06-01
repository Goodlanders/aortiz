<?php
require __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

// Conexión WooCommerce API destino
// ================================
$url_API_woo = 'https://goodland.es/tienda/';
$ck_API_woo = 'ck_e2827c3bac8cd01d7e981e0d260e10eeeb071c5a';
$cs_API_woo = 'cs_914971813893839925d23acf7e46621ccb0e5ab2';

$woocommerce = new Client(
    $url_API_woo,
    $ck_API_woo,
    $cs_API_woo,
    ['version' => 'wc/v3']
);
// ================================


// Conexión API origen
// ===================
//$url_API="http://edd4598.online-server.cloud/api/v1?productos=10&APIKEY=23sf8w8789r1r234l";
$url_API="http://localhost:3000/inventory";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url_API);

echo "➜ Obteniendo datos origen ... \n";
$items_origin = curl_exec($ch);
curl_close($ch);

if ( ! $items_origin ) {
    exit('❗Error en API origen');
}
// ===================


// Obtenemos datos de la API de origen
$items_origin = json_decode($items_origin, true);

// formamos el parámetro de lista de SKUs a actualizar
$param_sku ='';
foreach ($items_origin as $item){
    $param_sku .= $item['WREF_NUM'] . ',';
}

echo "➜ Obteniendo los ids de los productos... \n";
// Obtenemos todos los productos de la lista de SKUs
$products = $woocommerce->get('products/?sku='. $param_sku);

// Construimos la data en base a los productos recuperados
$item_data = [];
foreach($products as $product){

    // Filtramos el array de origen por sku
    $sku = $product->sku;
    $search_item = array_filter($items_origin, function($item) use($sku) {
        return $item['WREF_NUM'] == $sku;
    });
    $search_item = reset($search_item);

    // Formamos el array a actualizar
    $item_data[] = [
        'id' => $product->id,
        'regular_price' => $search_item['WPVP'],
        'sale_price' => $search_item['WREB'],
        'stock_quantity' => $search_item['WPARES'],
    ];



}

// Construimos información a actualizar en lotes
$data = [
    'update' => $item_data,
];

echo "➜ Actualización en lote ... \n";
// Actualización en lotes
//$result = $woocommerce->post('products/batch', $data);

$result = $woocommerce->post('products/10/variations/batch', $data);

if (! $result) {
    echo("❗Error al actualizar productos \n");
} else {
    print("✔ Productos actualizados correctamente \n");
}
