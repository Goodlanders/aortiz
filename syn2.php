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


class Servicio_Update
{
	
	function __construct()
	{
	    require_once '../wp-config.php';
		$this->hostname_db = DB_HOST;
        $this->database_db = DB_NAME;
        $this->username_db = DB_USER;
        $this->password_db = DB_PASSWORD;
        $this->prefix=$table_prefix;
        $this->conexion = new mysqli($this->hostname_db, $this->username_db, $this->password_db, $this->database_db);
	}

	function update_producto($data)
	{
       $query="SELECT meta_id,post_id,meta_key,meta_value FROM ".$this->prefix."postmeta where post_id=(SELECT post_id FROM ".$this->prefix."postmeta where meta_value='".$data['sku']."');";
       $this->conexion->set_charset("utf8");  
       $datos=mysqli_query($this->conexion,$query);
       
       while($row = mysqli_fetch_array($datos,MYSQLI_ASSOC)) {
           
           if($row['meta_key']=='_stock'){
               $query1="UPDATE ".$this->prefix."postmeta SET meta_value = '".$data['stock']."' WHERE meta_key='".$row['meta_key']."' AND  post_id =". $row['post_id'].";";
               $datos1=mysqli_query($this->conexion,$query1);
           }
           if($row['meta_key']=='_regular_price'){//Precio Normal
               $query2="UPDATE ".$this->prefix."postmeta SET meta_value = '".$data['regular_price']."' WHERE meta_key='".$row['meta_key']."' AND  post_id =". $row['post_id'].";";
               $datos2=mysqli_query($this->conexion,$query2);
           }
           if($row['meta_key']=='_sale_price'){//Precio rebajado ($) Horario
               $query3="UPDATE ".$this->prefix."postmeta SET meta_value = '".$data['sale_price']."' WHERE meta_key='".$row['meta_key']."' AND  post_id =". $row['post_id'].";";
               $datos3=mysqli_query($this->conexion,$query3);
           }
       }
  
       
	}
	
	function productos()
	{
	    // Conexión API origen
        // ===================
        $url_API="http://edd4598.online-server.cloud/api/v1?productos=100&APIKEY=23sf8w8789r1r234l";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url_API);
        echo "➜ Obteniendo datos origen ... \n";
        $items_origin = curl_exec($ch);
        curl_close($ch);
        if ( ! $items_origin ) { exit('❗Error en API origen');}
        // ===================
        // Obtenemos datos de la API de origen
         $items_origin = json_decode($items_origin, true);
        // formamos el parámetro de lista de SKUs a actualizar
        foreach ($items_origin as $item){
            
            $datos = [
                       "sku_PRINCIPAL" => $item['WREF'],
                       "sku" => $item['WREF_NUM'],
                       "attribute_pa_talla" => $item['WTALLA'],
                       "stock" => $item['WPARES'],
                       "regular_price" => $item['WPVP'],
                       "sale_price" => $item['WREB']
                     ];
            $this->update_producto($datos);
        }
         print("✔ Productos actualizados  \n");
        
	}
	
	
	
	

	
}

$servicio =new Servicio_Update();
$servicio->productos();








