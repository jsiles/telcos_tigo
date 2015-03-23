<?php
include ("./common2.php");
session_start();
$filename = "materiales.php";
$template_filename = "materiales.html";
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
valoresGrid_show();valoresRecord_show();
$tpl->pparse("main", false);
function valoresGrid_show()
{
  
  global $tpl;
  global $db;
  global $svaloresGridErr;
  $sWhere = "";
  $sOrder = "";
  $sSQL = "";
  $sFormTitle = "th_valoresiniciales";
  $HasParam = false;
  $iRecordsPerPage = 10;
  $iCounter = 0;
  $iPage = 0;
  $bEof = false;
  $iSort = "";
  $iSorted = "";
  $sDirection = "";
  $sSortParams = "";
  
  $iTmpI = 0;
  $iTmpJ = 0;
  $sCountSQL = "";
  $sActionFileName = "materiales.php";
  $tpl->set_var("TransitParams", "jue_id=".get_param("jue_id")."&FormvaloresGrid_Page=".get_param("FormvaloresGrid_Page")."&");
  $tpl->set_var("FormParams", "jue_id=".get_param("jue_id")."&FormvaloresGrid_Page=".get_param("FormvaloresGrid_Page")."&");
  $sOrder = " order by t.mat_id Asc";
  $iSort = get_param("FormvaloresGrid_Sorting");
  $iSorted = get_param("FormvaloresGrid_Sorted");
  if(!$iSort)
  {
    $tpl->set_var("Form_Sorting", "");
  }
  else
  {
    if($iSort == $iSorted)
    {
      $tpl->set_var("Form_Sorting", "");
      $sDirection = " DESC";
      $sSortParams = "FormvaloresGrid_Sorting=" . $iSort . "&FormvaloresGrid_Sorted=" . $iSort . "&";
    }
    else
    {
      $tpl->set_var("Form_Sorting", $iSort);
      $sDirection = " ASC";
      $sSortParams = "FormvaloresGrid_Sorting=" . $iSort . "&FormvaloresGrid_Sorted=" . "&";
    }
    if ($iSort == 1) $sOrder = " order by t.mat_material" . $sDirection;
    if ($iSort == 7) $sOrder = " order by t.mat_sw" . $sDirection;
  }
$fldvai_jue_id= get_param("jue_id");
  $sSQL = "select t.mat_id as t_mat_id, " . 
    "t.mat_material as t_mat_material, " . 
    "t.mat_sw as t_mat_sw " . 
    " from th_materiales t" . 
    " where t.mat_jue_id=$fldvai_jue_id ";
  $sSQL .= $sWhere . $sOrder;
  //echo $sSQL;
  if($sCountSQL == "")
  {
    $iTmpI = strpos(strtolower($sSQL), "select");
    $iTmpJ = strpos(strtolower($sSQL), "from") - 1;
    $sCountSQL = str_replace(substr($sSQL, $iTmpI + 6, $iTmpJ - $iTmpI - 6), " count(*) ", $sSQL);
    $iTmpI = strpos(strtolower($sCountSQL), "order by");
    if($iTmpI > 1) 
      $sCountSQL = substr($sCountSQL, 0, $iTmpI - 1);
  }
  $tpl->set_var("FormTitle", $sFormTitle);
  $tpl->set_var("FormAction", $sActionFileName);
  $tpl->set_var("SortParams", $sSortParams);
  $db->query($sSQL);
  $next_record = $db->next_record();
  if(!$next_record)
  {  


    $tpl->set_var("DListvaloresGrid", "");
    $tpl->set_var("DListvaloresAltGrid", "");
    
	$tpl->parse("valoresGridNoRecords", false);
    $tpl->set_var("valoresGridNavigator", "");
    $tpl->parse("FormvaloresGrid", false);
    return;
  }
  
  $avai_sw = explode(";", "A;Activo;I;Inactivo");
  $iRecordsPerPage = 10;
  $iCounter = 0;
  $iPage = get_param("FormvaloresGrid_Page");
  $db_count = get_db_value($sCountSQL);
  $dResult = intval($db_count) / $iRecordsPerPage;
  $iPageCount = intval($dResult);
  if($iPageCount < $dResult) $iPageCount = $iPageCount + 1;
  $tpl->set_var("valoresGridPageCount", $iPageCount);
  if(!strlen($iPage))
    $iPage = 1;
  else
  {
    if($iPage == "last") $iPage = $iPageCount;
  }
  if(($iPage - 1) * $iRecordsPerPage != 0)
  {
    do
    {
      $iCounter++;
    } while ($iCounter < ($iPage - 1) * $iRecordsPerPage && $db->next_record());
    $next_record = $db->next_record();
  }
  $iCounter = 0;
  $i=0;
  $tpl->set_var("th_valoresiniciales_TotalRecords", $db_count);
  while($next_record  && $iCounter < $iRecordsPerPage)
  {
    $fldvai_id_vai_id = $db->f("t_mat_id");
    $fldvai_monto = $db->f("t_mat_material");
    $fldvai_sw = $db->f("t_mat_sw");
    $fldvai_id= "Detalles";
    $next_record = $db->next_record();
    
      if ($i%2!=0)
      {
      $tpl->set_var("mat_id", tohtml($fldvai_id));
      $tpl->set_var("mat_unidad", tohtml("Unidades"));
     
	  $tpl->set_var("mat_id_URLLink", "materiales.php" );
      $tpl->set_var("mat_unidad_URLLink", "unidades.php" );
     
	  $tpl->set_var("Prmvai_id_mat_id", urlencode($fldvai_id_vai_id));
      $tpl->set_var("mat_material", tohtml($fldvai_monto));
      $fldvai_sw = get_lov_value($fldvai_sw, $avai_sw);
      $tpl->set_var("mat_sw", tohtml($fldvai_sw));
    $tpl->parse("DListvaloresGrid", true);
    $tpl->set_var("DListvaloresAltGrid", "");
      }
      else 
            {
      $tpl->set_var("Alt_mat_id", tohtml($fldvai_id));
      $tpl->set_var("Alt_mat_unidad", tohtml("Unidades"));
     
	  $tpl->set_var("Alt_mat_id_URLLink", "materiales.php" );
      $tpl->set_var("Alt_mat_unidad_URLLink", "unidades.php" );
     
	  $tpl->set_var("Alt_Prmvai_id_mat_id", urlencode($fldvai_id_vai_id));
      $tpl->set_var("Alt_mat_material", tohtml($fldvai_monto));
      $fldvai_sw = get_lov_value($fldvai_sw, $avai_sw);
      $tpl->set_var("Alt_mat_sw", tohtml($fldvai_sw));
    $tpl->parse("DListvaloresAltGrid", true);
    $tpl->set_var("DListvaloresGrid", "");
      }
      
    $tpl->parse("Grid", true);
    
$i++;
    
    $iCounter++;
  }
  $bEof = $next_record;
  if(!$bEof && $iPage == 1)
    $tpl->set_var("valoresGridNavigator", "");
  else 
  {
    if(!$bEof)
      $tpl->set_var("valoresGridNavigatorLastPage", "_");
    else
      $tpl->set_var("NextPage", ($iPage + 1));
    if($iPage == 1)
      $tpl->set_var("valoresGridNavigatorFirstPage", "_");
    else
      $tpl->set_var("PrevPage", ($iPage - 1));
    $tpl->set_var("valoresGridCurrentPage", $iPage);
    $tpl->parse( "valoresGridNavigator", false);
  }
  $tpl->set_var( "valoresGridNoRecords", "");
  $tpl->parse( "FormvaloresGrid", false);
}
function valoresRecord_action($sAction)
{
  global $db;
  global $tpl;
  global $sForm;
  global $svaloresRecordErr;
  $bExecSQL = true;
  $sActionFileName = "";
  $sWhere = "";
  $bErr = false;
  $fldvai_jue_id = "";
  $fldvai_atr_id = "";
  $fldvai_mer_id = "";
  $fldvai_cli_id = "";
  $fldvai_monto = "";
  $fldvai_periodo = "";
  $fldvai_sw = "";
  
  $fldFormvaloresGrid_Page = get_param("FormvaloresGrid_Page");
  $sActionFileName = "materiales.php?";
  $sParams ="FormvaloresGrid_Page=$fldFormvaloresGrid_Page&";
  if($sAction == "cancel")
  {
    header("Location: " . $sActionFileName);
    exit;
  }
  if($sAction == "update" || $sAction == "delete") 
  {
    $pPKvai_id = get_param("PK_mat_id");
    if( !strlen($pPKvai_id)) return;
    $sWhere = "mat_id=" . tosql($pPKvai_id, "Number");
  }
  $fldvai_jue_id = get_param("jue_id");
  $fldvai_material = get_param("mat_material");
  $fldvai_sw = get_param("mat_sw");

   if($sAction == "insert")
   {
      if ((strlen($fldvai_atr_id))&&(strlen($fldvai_mer_id))&&(strlen($fldvai_pro_id))&&(strlen($fldvai_cli_id))&&(strlen($fldvai_periodo)))
      {$sSQLValida = "select count(*) from th_materiales where ".
          "mat_jue_id=$fldvai_jue_id and mat_material='$fldvai_material'";
      $sValida = get_db_value($sSQLValida);
      //echo $sSQLValida;
      }
       
      if ($sValida!=0)   
      $svaloresRecordErr .= "Dato ya introducido anteriormente.<br>";
   }
  if($sAction == "insert" || $sAction == "update") 
  {
    
    if(!strlen($fldvai_material))
      $svaloresRecordErr .= "El valor en el campo material se requiere.<br>";

    if(!strlen($fldvai_sw))
      $svaloresRecordErr .= "El valor en el campo estado se requiere.<br>";

    if(strlen($svaloresRecordErr)) return;
  }
         $sParams .=  "jue_id=" .$fldvai_jue_id; 
         

  switch(strtolower($sAction)) 
  {
    case "insert":
        $sSQL = "insert into th_materiales (" . 
          "mat_jue_id," . 
          "mat_material," . 
          "mat_sw)" . 
          " values (" . 
          tosql($fldvai_jue_id, "Number") . "," . 
          tosql($fldvai_material, "Text") . "," . 
          tosql($fldvai_sw, "Text") . 
          ")";
    break;
    case "update":
        $sSQL = "update th_materiales set " .
          "mat_jue_id=" . tosql($fldvai_jue_id, "Number") .
          ",mat_material=" . tosql($fldvai_material, "Text") .
          ",mat_sw=" . tosql($fldvai_sw, "Text");
        $sSQL .= " where " . $sWhere;
    break;
    case "delete":
        $sSQL = "delete from th_materiales where " . $sWhere;
    break;
  }
  if(strlen($svaloresRecordErr)) return;
  if($bExecSQL)
    $db->query($sSQL);
  //echo $sSQL;
  header("Location: " . $sActionFileName. $sParams);
  exit;
}
function valoresRecord_show()
{
  global $db;
  global $tpl;
  global $sAction;
  global $sForm;
  global $svaloresRecordErr;

  
  $fldvai_id = "";
  $fldvai_jue_id = "";
  $fldvai_atr_id = "";
  $fldvai_sw = "";
  $sFormTitle = "th_valoresiniciales";
  $fldFormvaloresGrid_Page = get_param("FormvaloresGrid_Page");
  $sWhere = "";
  $fldvai_jue_id= get_param("jue_id");
  $bPK = true;
  if($svaloresRecordErr == "")
  {
    $fldvai_id = get_param("mat_id");
    $pvai_id = get_param("mat_id");
    $tpl->set_var("valoresRecordErr", "");
  }
  else
  {
    $fldvai_id = strip(get_param("mat_id"));
    $fldvai_jue_id = strip(get_param("mat_jue_id"));
    $fldvai_material = strip(get_param("mat_material"));
    $fldvai_sw = strip(get_param("mat_sw"));
    $pvai_id = get_param("PK_mat_id");
    $tpl->set_var("svaloresRecordErr", $svaloresRecordErr);
    $tpl->set_var("FormTitle", $sFormTitle);
    $tpl->parse("valoresRecordErr", false);
  }
  
  if( !strlen($pvai_id)) $bPK = false;
  
  $sWhere .= "mat_id=" . tosql($pvai_id, "Number");
  $tpl->set_var("PK_mat_id", $pvai_id);
  $tpl->set_var("FormTitle", $sFormTitle);
  $sSQL = "select * from th_materiales where " . $sWhere;  
//echo $sSQL;
  $db->query($sSQL);
  $bIsUpdateMode = ($bPK && !($sAction == "insert" && $sForm == "valores") && $db->next_record());
  if($bIsUpdateMode)
  {
      $fldvai_id = $db->f("mat_id");        
    if($svaloresRecordErr == "") 
    {
      $fldvai_id = $db->f("mat_id");
      $fldvai_jue_id = $db->f("mat_jue_id");
      $fldvai_sw = $db->f("mat_sw");      
	  $fldvai_material = $db->f("mat_material");
    }
    $tpl->set_var("valoresRecordInsert", "");
    $tpl->parse("valoresRecordEdit", false);
  }
  else
  {
    if($valoresRecordErr == "")
    {
      $fldvai_id = tohtml(get_param("mat_id"));
    }
    $tpl->set_var("valoresRecordEdit", "");
    $tpl->parse("valoresRecordInsert", false);
  }
  $tpl->parse("valoresRecordCancel", false);
if (!$bIsUpdateMode)
{  
  $fldvai_monto = get_param("mat_material");
  $fldvai_sw = get_param("mat_sw");
}
  
    $tpl->set_var("mat_id", tohtml($fldvai_id));
    $tpl->set_var("FormvaloresGrid_Page", $fldFormvaloresGrid_Page); 
    $tpl->set_var("mat_material", tohtml($fldvai_material));
    $tpl->set_var("valoresRecordLBvai_sw", "");
    $tpl->set_var("jue_id", get_param("jue_id"));
    $LOV = explode(";", ";Seleccionar Valor;A;Activo;I;Inactivo");
  
    if(sizeof($LOV)%2 != 0) 
      $array_length = sizeof($LOV) - 1;
    else
      $array_length = sizeof($LOV);
    reset($LOV);
    for($i = 0; $i < $array_length; $i = $i + 2)
    {
      $tpl->set_var("ID", $LOV[$i]);
      $tpl->set_var("Value", $LOV[$i + 1]);
      if($LOV[$i] == $fldvai_sw) 
        $tpl->set_var("Selected", "SELECTED");
      else
        $tpl->set_var("Selected", "");
      $tpl->parse("valoresRecordLBvai_sw", true);
    }
  $tpl->parse("FormvaloresRecord", false);
}
?>
