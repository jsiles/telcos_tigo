<?php

//Include Files @0-6CA7C540
include(RelativePath . "/Classes.php");
include(RelativePath . "/db_mysql.php");
//End Include Files

//Connection Settings @0-3511B515
$CCConnectionSettings = array (
    "siges" => array(
        "Type" => "MySQL",
        "Database" => "simulado_manufactura",
        "Host" => "localhost",
        "Port" => "3306",
        "User" => "simulado_manufac",
        "Password" => "manufactura",
        "Persistent" => true,
        "DateFormat" => array("yyyy", "-", "mm", "-", "dd", " ", "HH", ":", "nn", ":", "ss"),
        "BooleanFormat" => array(1, 0, ""),
        "Uppercase" => false
    )
);
//End Connection Settings

//Initialize Common Variables @0-E85B60D8
$PHPVersion = explode(".",  phpversion());
if (($PHPVersion[0] < 4) || ($PHPVersion[0] == 4  && $PHPVersion[1] < 1)) {
    echo "Sorry. This program requires PHP 4.1 and above to run.<br>You may upgrade your php at <a href='http://www.php.net/downloads.php'>http://www.php.net/downloads.php</a>";
    exit;
}
session_start();
header('Pragma: ');
header('Cache-control: ');
header('Expires: ');
define("TemplatePath", RelativePath);
define("ServerURL", ((isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") ? "https://" : "http://" ). ($_SERVER["HTTP_HOST"] ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"]) . ($_SERVER["SERVER_PORT"] != 80 ? ":" . $_SERVER["SERVER_PORT"] : "") . substr($_SERVER["PHP_SELF"], 0, strlen($_SERVER["PHP_SELF"]) - strlen(PathToCurrentPage . FileName)) . "/");
define("SecureURL", "");

$FileEncoding = "CP1252";
$CCSLocales = new clsLocales(RelativePath);
$CCSLocales->AddLocale("es", Array("es", "ES", array("True", "False", ""), 2, ",", ".", array("enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"), array("ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"), array("domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado"), array("dom", "lun", "mar", "mié", "jue", "vie", "sáb"), array("D", "L", "M", "M", "J", "V", "S"), array("dd", "/", "mm", "/", "yyyy"), array("dddd", ", ", "dd", " de ", "mmmm", " de ", "yyyy"), array("H", ":", "nn"), array("H", ":", "nn", ":", "ss"), "", "", 1, false, "", "windows-1252", "CP1252"));
$CCSLocales->DefaultLocale = strtolower("es");
$CCSLocales->Init();
$Charset = "";

if ($PHPLocale = $CCSLocales->GetFormatInfo("PHPLocale"))
    setlocale(LC_ALL, $PHPLocale);
CCConvertDataArrays();
$CCProjectStyle = "";
//for compatibility
$ShortWeekdays = $CCSLocales->GetFormatInfo("WeekdayShortNames");
$Weekdays = $CCSLocales->GetFormatInfo("WeekdayNames");
$ShortMonths =  $CCSLocales->GetFormatInfo("MonthShortNames");
$Months = $CCSLocales->GetFormatInfo("MonthNames");

define("ccsInteger", 1);
define("ccsFloat", 2);
define("ccsSingle", ccsFloat); //alias
define("ccsText", 3);
define("ccsDate", 4);
define("ccsBoolean", 5);
define("ccsMemo", 6);

define("ccsGet", 1);
define("ccsPost", 2);

define("ccsTimestamp", 0);
define("ccsYear", 1);
define("ccsMonth", 2);
define("ccsDay", 3);
define("ccsHour", 4);
define("ccsMinute", 5);
define("ccsSecond", 6);
define("ccsMilliSecond", 7);
define("ccsAmPm", 8);
define("ccsShortMonth", 9);
define("ccsFullMonth", 10);
define("ccsWeek", 11);
define("ccsGMT", 12);
define("ccsAppropriateYear", 13);

$DefaultDateFormat = array("dd", "/", "mm", "/", "yyyy");

$MainPage = new clsMainPage();
//End Initialize Common Variables

//siges Connection Class @-ACB4BAC6
class clsDBsiges extends DB_MySQL
{

    var $DateFormat;
    var $BooleanFormat;
    var $LastSQL;
    var $Errors;

    var $RecordsCount;
    var $RecordNumber;
    var $PageSize;
    var $AbsolutePage;

    var $SQL = "";
    var $Where = "";
    var $Order = "";

    var $Parameters;
    var $wp;

    function clsDBsiges()
    {
        $this->Initialize();
    }

    function Initialize()
    {
        $this->AbsolutePage = 0;
        $this->PageSize = 0;
        global $CCConnectionSettings;
        $Configuration = $CCConnectionSettings["siges"];
        $this->DB = $Configuration["Type"];
        $this->DBDatabase = $Configuration["Database"];
        $this->DBHost = $Configuration["Host"];
        $this->DBPort = $Configuration["Port"];
        $this->DBUser = $Configuration["User"];
        $this->DBPassword = $Configuration["Password"];
        $this->Persistent = $Configuration["Persistent"];
        $this->DateFormat = $Configuration["DateFormat"];
        $this->BooleanFormat = $Configuration["BooleanFormat"];
        $this->Uppercase = $Configuration["Uppercase"];
        $this->RecordsCount = 0;
        $this->RecordNumber = 0;
        $this->LastSQL = "";
        $this->Errors = New clsErrors();
    }

    function MoveToPage($Page)
    {
        global $CCSLocales;
        if($this->RecordNumber == 0 && $this->PageSize != 0 && $Page != 0 && $Page != 1)
            if( !$this->seek(($Page-1) * $this->PageSize)) {
                $this->Errors->addError($CCSLocales->GetText('CCS_CannotSeek'));
                $this->RecordNumber = $this->Row;
            } else {
                $this->RecordNumber = ($Page-1) * $this->PageSize;
            }
    }
    function PageCount()
    {
        return $this->PageSize && $this->RecordsCount != "CCS not counted" ? ceil($this->RecordsCount / $this->PageSize) : 1;
    }

    function ToSQL($Value, $ValueType)
    {
        if(($ValueType == ccsDate && is_array($Value)) || strlen($Value) || ($ValueType == ccsBoolean && is_bool($Value)))
        {
            if($ValueType == ccsInteger || $ValueType == ccsFloat)
            {
                return doubleval(str_replace(",", ".", $Value));
            }
            else if($ValueType == ccsDate)
            {
                if (is_array($Value)) {
                    $Value = CCFormatDate($Value, $this->DateFormat);
                }
                return "'" . addslashes($Value) . "'";
            }
            else if($ValueType == ccsBoolean)
            {
                if(is_bool($Value))
                    $Value = CCFormatBoolean($Value, $this->BooleanFormat);
                else if(is_numeric($Value))
                    $Value = intval($Value);
                else
                    $Value = "'" . addslashes($Value) . "'";
                return $Value;
            }
            else
            {
                return "'" . addslashes($Value) . "'";
            }
        }
        else
        {
            return "NULL";
        }
    }

    function SQLValue($Value, $ValueType)
    {
        if ($ValueType == ccsDate && is_array($Value)) {
            $Value = CCFormatDate($Value, $this->DateFormat);
        }
        if(!strlen($Value))
        {
            return "";
        }
        else
        {
 
