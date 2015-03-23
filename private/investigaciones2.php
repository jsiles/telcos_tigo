<?php
include ("./common2.php");
session_start();
$filename = "investigaciones2.php";
$template_filename = "investigaciones2.html";
$sAction = get_param("FormAction");
$sForm = get_param("FormName");
$svaloresRecordErr = "";
switch ($sForm) {
  case "valoresRecord":
    valoresRecord_action($sAction);
  break;
}
$tpl = new Template($app_path);
$tpl->load_file($template_filename, "main");
$tpl->set_var("FileName", $filename);
//valoresGrid_show();
valoresRecord_show();
$tpl->pparse("main", false);
function valoresRecord_action($sAction)
{
  global $db;
  global $tpl;
  global $sForm;
  global $svaloresRecordErr;
  $bExecSQL = false;
  $sActionFileName = "";
  $sWhere = "";
  $bErr = false;
  $fldperiodo = get_param("periodo");
  $fldcantidad = get_param("cant"); 
  $fldperiodoinicial = get_param("per_ini"); 
  $fldjuego = get_param("jue_id"); 
  $flddat_periodo = get_param("dat_periodo");
  $costo = get_param("costo");
  $costo_exclusividad = get_param("costo_exclusividad");
  $cantidad = get_param("cantidad");
  $pdf = get_param("pdf");
  $fldproducto = get_param("productoArray");   
  $fldmercado = get_param("mercadoArray");
  $eliminar = get_param("eliminar");   
    
  $cantProducto = count($fldproducto);
  $cantMercado = count($fldmercado);
  //echo $cantidad."prb";
  //print_r($_FILES);
  $TMPFILES = $_FILES["pdf"]["tmp_name"];
  $FILES = $_FILES["pdf"]["name"];
  
 // print_r($eliminar);
  //print_r($FILES);
 // print_r($fldproducto);
 // print_r($fldmercado);
//             $query = "select count(*) tb_investigaciones where inv_jue_id=$juego and inv_pro_id=$pro_id and inv_mer_id= $mer_id, $per_id, 0,0,0, '',1)";

  if (is_array($eliminar))
  {
  foreach ($eliminar as $key => $value)
  {
	list($juego,$producto,$mercado,$periodo) = explode("-",$value);
		
		//$sSQL="update tb_investigaciones set inv_cantidad=0, inv_saldo=0.00, inv_costo=0.00, inv_costoexclusividad=0.00 where inv_jue_id=$juego and inv_pro_id=$producto and inv_mer_id= $mercado and inv_per_id=$periodo";
		//$db->query($sSQL);
		
		$sSQL="update tb_investigaciones set inv_pdf='', inv_cantidad=0, inv_saldo=0.00, inv_costo=0.00, inv_costoexclusividad=0.00 where inv_jue_id=$juego and inv_pro_id=$producto and inv_mer_id= $mercado and inv_per_id=$periodo";
		$db->query($sSQL);
		
		//echo $sSQL;

		$inv_id = get_db_value("select inv_id from tb_investigaciones where inv_jue_id=$juego and inv_pro_id=$producto and inv_mer_id= $mercado and inv_per_id=$periodo");
		//echo $inv_id;
		if ($inv_id)
		{
			$sSQL = "delete from tb_compras where com_inv_id=$inv_id";
			$db->query($sSQL);
		}
        	
  }
  }
  else
  {
  $k=0;
  for ($i=0;$i<$cantProducto;$i++)
  { 
    for ($j=0;$j<$cantMercado;$j++)
    { 
        if ($costo[$k]=='ND') $costo[$k]=0;
        if ($costo_exclusividad[$k]=='ND') $costo_exclusividad[$k]=0;
        if ($cantidad[$k]=='ND') $cantidad[$k]=0;
		
        if (!$costo[$k]) $costo[$k]=0;
        if (!$costo_exclusividad[$k]) $costo_exclusividad[$k]=0;
        if (!$cantidad[$k]) $cantidad[$k]=0;

        if (strlen($costo[$k])==0) $costo[$k]=0;
        if (strlen($costo_exclusividad[$k])==0) $costo_exclusividad[$k]=0;
        if (strlen($cantidad[$k])==0) $cantidad[$k]=0;


        if ($FILES[$k]=='') 
            $sSQL="update tb_investigaciones set inv_cantidad=$cantidad[$k], inv_saldo=$cantidad[$k], inv_costo=$costo[$k], inv_costoexclusividad=$costo_exclusividad[$k] where inv_jue_id=$fldjuego and inv_pro_id=$fldproducto[$i] and inv_mer_id= $fldmercado[$j] and inv_per_id=$fldperiodo";
        else
            {
                $nombrepdf= 'temp/Pdf'. $fldjuego."-".$fldproducto[$i]."-".$fldmercado[$j]."-".$fldperiodo.'.pdf';
                $nombrepdf1= 'Pdf'. $fldjuego."-".$fldproducto[$i]."-".$fldmercado[$j]."-".$fldperiodo.'.pdf';
                $sSQL="update tb_investigaciones set inv_cantidad=$cantidad[$k], inv_saldo=$cantidad[$k], inv_costo=$costo[$k], inv_costoexclusividad=$costo_exclusividad[$k], inv_pdf='$nombrepdf1' where inv_jue_id=$fldjuego and inv_pro_id=$fldproducto[$i] and inv_mer_id= $fldmercado[$j] and inv_per_id=$fldperiodo";
                move_uploaded_file($TMPFILES[$k] , $nombrepdf);
                chmod( $nombrepdf , 0755 );
            }
        //echo "<br>";
        $db->query($sSQL);
        $k++; 
       //echo $sSQL;
    }
  }
  }
  header("Location: investigaciones.php?jue_id=$fldjuego&per_ini=$fldperiodoinicial&cant=$fldcantidad");
  exit;
  
}
function valoresRecord_show()
{
  global $db,$db2,$db3,$db4;
  global $tpl;
  global $sAction;
  global $sForm;
  global $svaloresRecordErr;

  $fldperiodo = get_param("dat_periodo");
  $dat_periodo = get_param("dat_periodo");
  $fldvai_id = "";
  $fldvai_jue_id = "";
  $fldvai_atr_id = "";
  $fldvai_pro_id = "";
  $fldvai_mer_id = "";
  $fldvai_cli_id = "";
  $fldvai_monto = "";
  $fldvai_periodo = "";
  $fldvai_sw = "";
  $sFormTitle = "th_valoresiniciales";
  $fldFormvaloresGrid_Page = get_param("FormvaloresGrid_Page");
  $sWhere = "";
  $fldvai_jue_id= get_param("jue_id");
  $bPK = true;
  $periodoinicial = get_param("per_ini");
  $periodocantidad = get_param("cant") ;
  $juego = get_param("jue_id");

//  $per_id=2;
      for($i=0;$i<$periodocantidad;$i++)
        {            
            $periodo[$periodoinicial+$i] = $periodoinicial+$i;
        }
        if(is_array($periodo))
                {
                  reset($periodo);
//                            $tpl->set_var("ID", "");
//                            $tpl->set_var("Value", "Seleccionar valor");
//                            $tpl->parse("Periodo", true);
                  $i=0;                                   
                  while(list($key, $value) = each($periodo))
                  {
                    if ($i==0&&$dat_periodo=='') $fldperiodo = $key;
                    $tpl->set_var("ID", $key);
                    $tpl->set_var("Value", $value);
                    if($key == $dat_periodo)
                      $tpl->set_var("Selected", "SELECTED" );
                    else
                      $tpl->set_var("Selected", "");
                    $tpl->parse("Periodo", true);
                    $i++;
                  }
                }

  
  $tpl->set_var("PK_inv_id", $pvai_id);
  $tpl->set_var("FormTitle", $sFormTitle);

  $fldvai_inicial = get_param("per_ini");
  $fldvai_cantidad = get_param("cant");
  $tpl->set_var("juego", dlookup("tb_juegos", "jue_nombre" , "jue_id=$fldvai_jue_id"));

 /* $sSQL = "select * from tb_mercados where mer_jue_id=$juego and mer_sw=1";
          $db->query($sSQL);
        //  echo $sSQL1; die;
  $j=0;      
  while($db->next_record())
   {
          $mer_nombre = $db->f("mer_nombre");
          $tpl->set_var("mercado",$mer_nombre);          
          $tpl->parse("Mercados",true);
          $tpl->parse("Etiquetas",true);
          $j++;
   }
*/
//          $tpl->set_var("mercado","MERCADOS");          
//          $tpl->parse("Mercados",true);
          $tpl->parse("Etiquetas",true);
          $j++;

   $j = $j*4;
   
   $tpl->set_var("val",$j);
   $tpl->set_var("val1",$j+1);
  $sSQL = "select * from tb_productos where pro_jue_id=$juego and pro_sw=1";
  $db->query($sSQL);
  while($db->next_record())
  {
      $resProducto[$db->f("pro_id")] = $db->f("pro_nombre");
  }
  $resProducto[66]="TODOS LOS PRODUCTOS";
/*  $sSQL = "select * from tb_mercados where mer_jue_id=$juego and mer_sw=1";
  $db->query($sSQL);
  while($db->next_record())
  {
      $resMercado[$db->f("mer_id")] = $db->f("mer_nombre");
  }
  $resMercado[66] = "TODOS LOS MERCADOS";
  */
  $resMercado[66] = "Mercado";
  
  
    $k=0;
    $l=0;
//      $db->f("pro_nombre")="TODOS";
//      $db->f("pro_id")="66";  
    // $producto = array();
  foreach($resProducto as $pro_id=>$pro_nombre)
  {
/*      $pro_nombre = $db->f("pro_nombre");
      $pro_id = $db->f("pro_id");
      $sSQL1 = "select * from tb_mercados where mer_jue_id=$juego and mer_sw=1";
      $db2->query($sSQL1);*/
      $tpl->set_var("producto",$pro_nombre);
//      while($db2->next_record())
      foreach($resMercado as $mer_id=>$mer_nombre)
      {
         //$mer_id = $db2->f("mer_id");
         $sSQL2 = "select * from tb_investigaciones where inv_pro_id=$pro_id and inv_mer_id=$mer_id and inv_jue_id=$juego and inv_per_id=$fldperiodo and inv_sw=1";
         //echo $sSQL2;die;  array
         $db3->query($sSQL2);
         if ($db3->next_record())
         {
             $costo = $db3->f("inv_costo");
             //echo $costo."hkj";die;
             $tpl->set_var("costo",($db3->f("inv_costo")==0)?'ND':$db3->f("inv_costo"));
             $tpl->set_var("costo_exclusividad",($db3->f("inv_costoexclusividad")==0)?'ND':$db3->f("inv_costoexclusividad"));
             $tpl->set_var("cantidad",($db3->f("inv_cantidad")==0)?'ND':$db3->f("inv_cantidad"));
             $tpl->set_var("pdfname",$db3->f("inv_pdf"));
			 if (strlen($db3->f("inv_pdf"))>0)
             $tpl->set_var("eliminar","Eliminar <input name=\"eliminar[]\" type=\"checkbox\" value=\"$juego-$pro_id-$mer_id-$fldperiodo\">");
			 else
			 $tpl->set_var("eliminar","");
            //<<div></div> $tpl->set_var("pdf","valor cargado");
         }
         else
         {
             $sql="insert into tb_investigaciones values (null, $juego, $pro_id, $mer_id, $fldperiodo, 0,0,0,0, '',1)";
             //echo $sql;die;
             $db4->query($sql);
             $tpl->set_var("costo","ND");
             $tpl->set_var("costo_exclusividad","ND");
             $tpl->set_var("cantidad","ND");
             //$tpl->set_var("id",$k);
             $tpl->set_var("pdf","ND");
         }
         $k++;
         
         $tpl->parse("Data",true);
         if ($l==0)
         {
             $tpl->set_var("mercadoArray",$mer_id);
             $tpl->parse("ArrayMercado",true);
         }
      }
      $tpl->parse("Productos",true);
      $tpl->set_var("Data","");
      
      $l++;
  $tpl->set_var("productoArray", $pro_id);
  $tpl->parse("ArrayProducto",true);
 }
 
  $tpl->set_var("vai_inicial", tohtml($fldvai_inicial));
  $fldvai_final =   $fldvai_inicial + $fldvai_cantidad -1;
  $tpl->set_var("vai_final", tohtml($fldvai_final));
  $tpl->set_var("vai_cantidad", tohtml($fldvai_cantidad));
  $tpl->set_var("vai_id", tohtml($fldvai_id));
  $tpl->set_var("vai_jue_id", tohtml($fldvai_jue_id));
  $tpl->set_var("periodo",$fldperiodo);
  $tpl->parse("FormvaloresRecord", false);
}
?>
