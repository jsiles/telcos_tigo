<?Php
//$arrayMateriales = array(1 => "TELAS", 2 => "MATERIALES", 3 => "QUIMICOS");
//$arrayUnidades = array(1 => array( 4 => "\$M/4 ROLLOS", 8 => "\$M/8 ROLLOS"), 2 => array( 40 => "\$M/40 KITS", 80 => "\$M/80 KITS"), 3 => array( 40 => "\$M/40 KILOGRAMOS", 80 => "\$M/80 KILOGRAMOS"));

$id=(get_param("jue_id"))?get_param("jue_id"):get_param("id");

$sSQL = "select mat_id, mat_material from th_materiales where mat_jue_id=$id";
$arrayMateriales = db_fill_array($sSQL);

if(is_array($arrayMateriales))
{
foreach($arrayMateriales as $key=>$value)
{
	$sSQL2= "select uni_valor, uni_unidad from th_unidades where uni_mat_id=$key";
	$arrayUnidades[$key] = db_fill_array($sSQL2);
}
}else $arrayUnidades = null; 	
?>
