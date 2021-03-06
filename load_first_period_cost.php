<?php

include("inc/inc.php");
//include("inc/inc.cobra.php");

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 4/13/2017
 * Time: 2:52 PM
 */


function getStageInsertSQL($table_name, $schema){
    $insert_sql = "
        insert into $schema.".$table_name." (
        ship_code,
        ca,
        wp,
        descr,
        c1,
        c2,
        c3,
        s,
        p,
        a,
        bac,
        eac,
        s_hours,
        p_hours,
        a_hours,
        bac_hours,
        eac_hours,
        pc) values
 ";
    return $insert_sql;
}
function insertCobraCostDataStage($ship_code, $schema, $table_name, $ship_code_wc){
    $sql = "
    select
        PROGRAM,
        CA1,
        C1,
        C2,
        C3,
        WP,
        DESCRIP,
        BCWS,
        BCWP,
        ACWP,
        BAC,
        EAC,
        BCWS_HRS,
        BCWP_HRS,
        ACWP_HRS,
        BAC_HRS,
        EAC_HRS,
        PC_COMP
    from CAWP where PROGRAM = '$ship_code_wc'
    ";
    $rs = dbCallCobra($sql);
    $insert_sql = getStageInsertSQL($table_name,$schema);
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF)
    {
        $ca        = addslashes(trim($rs->fields["CA1"]));
        $wp        = addslashes(trim($rs->fields["WP"]));
        $descr     = addslashes(trim($rs->fields["DESCRIP"]));
        $c1        = addslashes(trim($rs->fields["C1"]));
        $c2        = addslashes(trim($rs->fields["C2"]));
        $c3        = addslashes(trim($rs->fields["C3"]));
        $s         = formatNumber4decCobra($rs->fields["BCWS"]);
        $p         = formatNumber4decCobra($rs->fields["BCWP"]);
        $a         = formatNumber4decCobra($rs->fields["ACWP"]);
        $bac       = formatNumber4decCobra($rs->fields["BAC"]);
        $eac       = formatNumber4decCobra($rs->fields["EAC"]);
        $s_hours   = formatNumber4decCobra($rs->fields["BCWS_HRS"]);
        $p_hours   = formatNumber4decCobra($rs->fields["BCWP_HRS"]);
        $a_hours   = formatNumber4decCobra($rs->fields["ACWP_HRS"]);
        $bac_hours = formatNumber4decCobra($rs->fields["BAC_HRS"]);
        $eac_hours = formatNumber4decCobra($rs->fields["EAC_HRS"]);
        $pc        = formatNumber4decCobra($rs->fields["PC_COMP"]);

        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$descr',
            '$c1',
            '$c2',
            '$c3',
            $s,
            $p,
            $a,
            $bac,
            $eac,
            $s_hours,
            $p_hours,
            $a_hours,
            $bac_hours,
            $eac_hours,
            $pc),";
        if($i==500){
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql,$schema);

            $sql = $insert_sql;
            $i=0;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i!=500){
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql,$schema);
        $sql = $insert_sql;
    }

}
function returnCurVal($cur, $prev){
    $cur_val = formatNumber4decCobra($cur-$prev);
    return $cur_val;
}
function returnCurInsertSQL($schema, $cur_table_name){
    $insert_sql = "
        insert into $schema.".$cur_table_name." (
        ship_code,
        ca,
        wp,
        descr,
        c1,
        c2,
        c3,
        s,
        s_cur,
        p,
        p_cur,
        a,
        a_cur,
        bac,
        bac_diff,
        eac,
        eac_diff,
        s_hours,
        s_hours_cur,
        p_hours,
        p_hours_cur,
        a_hours,
        a_hours_cur,
        bac_hours,
        bac_hours_diff,
        eac_hours,
        eac_hours_diff,
        pc) values
 ";
    return $insert_sql;
}
function insertCobraCostDataCur($ship_code, $schema, $stage_table_name, $cur_table_name, $prev_table_name){
$sql = "
        select
        stage.ship_code,
            stage.ca,
            stage.wp,
            stage.descr,
            stage.c1,
            stage.c2,
            stage.c3,
            stage.s,
            prev.s s_prev,
            stage.p,
            prev.p p_prev,
            prev.a a_prev,
            stage.a,
            prev.bac bac_prev,
            stage.bac,
            prev.eac eac_prev,
            stage.eac,
            prev.s_hours s_hours_prev,
            stage.s_hours,
            prev.p_hours p_hours_prev,
            stage.p_hours,
            prev.a_hours a_hours_prev,
            stage.a_hours,
            prev.bac_hours bac_hours_prev,
            stage.bac_hours,
            prev.eac_hours eac_hours_prev,
            stage.eac_hours,
            stage.pc
        from cost2.".$stage_table_name." stage
        left join
        $schema.$prev_table_name prev on
        stage.ship_code = prev.ship_code
        and stage.ca = prev.ca
        and stage.wp = prev.wp
 ";
$sql = "
        select
        stage.ship_code,
            stage.ca,
            stage.wp,
            stage.descr,
            stage.c1,
            stage.c2,
            stage.c3,
            stage.s,
            stage.p,
            stage.a,
            stage.bac,
            stage.eac,
            stage.s_hours,
            stage.p_hours,
            stage.a_hours,
            stage.bac_hours,
            stage.eac_hours,
            stage.pc
        from cost2.".$stage_table_name." stage
            where stage.ship_code = $ship_code
        ";
$rs = dbCall($sql, $schema);
print $sql;
$insert_sql = returnCurInsertSQL($schema, $cur_table_name);
$sql = $insert_sql;
$i = 0;
while (!$rs->EOF)
{
    $ca             = addslashes(trim($rs->fields["ca"]));
    $wp             = addslashes(trim($rs->fields["wp"]));
    $descr          = addslashes(trim($rs->fields["descr"]));
    $c1             = addslashes(trim($rs->fields["c1"]));
    $c2             = addslashes(trim($rs->fields["c2"]));
    $c3             = addslashes(trim($rs->fields["c3"]));
    $s              = formatNumber4decCobra($rs->fields["s"]);
    //$s_prev         = formatNumber4decCobra($rs->fields["s_prev"]);
    $s_cur          = returnCurVal($s, $s_prev);
    $p              = formatNumber4decCobra($rs->fields["p"]);
    //$p_prev         = formatNumber4decCobra($rs->fields["p_prev"]);
    $p_cur          = returnCurVal($p, $p_prev);
    $a              = formatNumber4decCobra($rs->fields["a"]);
    //$a_prev         = formatNumber4decCobra($rs->fields["a_prev"]);
    $a_cur          = returnCurVal($a, $a_prev);
    $bac            = formatNumber4decCobra($rs->fields["bac"]);
    //$bac_prev       = formatNumber4decCobra($rs->fields["bac_prev"]);
    $bac_diff       = returnCurVal($bac, $bac_prev);
    $eac            = formatNumber4decCobra($rs->fields["eac"]);
    //$eac_prev       = formatNumber4decCobra($rs->fields["eac_prev"]);
    $eac_diff       = returnCurVal($eac, $eac_prev);
    $s_hours        = formatNumber4decCobra($rs->fields["s_hours"]);
    //$s_hours_prev   = formatNumber4decCobra($rs->fields["s_hours_prev"]);
    $s_hours_cur    = returnCurVal($s_hours, $s_hours_prev);
    $p_hours        = formatNumber4decCobra($rs->fields["p_hours"]);
    //$p_hours_prev   = formatNumber4decCobra($rs->fields["p_hours_prev"]);
    $p_hours_cur    = returnCurVal($p_hours, $p_hours_prev);
    $a_hours        = formatNumber4decCobra($rs->fields["a_hours"]);
    //$a_hours_prev   = formatNumber4decCobra($rs->fields["a_hours_prev"]);
    $a_hours_cur    = returnCurVal($a_hours, $a_hours_prev);
    $bac_hours      = formatNumber4decCobra($rs->fields["bac_hours"]);
    //$bac_hours_prev = formatNumber4decCobra($rs->fields["bac_hours_prev"]);
    $bac_hours_diff = returnCurVal($bac_hours, $bac_hours_prev);
    $eac_hours      = formatNumber4decCobra($rs->fields["eac_hours"]);
    //$eac_hours_prev = formatNumber4decCobra($rs->fields["eac_hours_prev"]);
    $eac_hours_diff = returnCurVal($eac_hours, $eac_hours_prev);
    $pc             = formatNumber4decCobra($rs->fields["pc"]);

    $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$descr',
            '$c1',
            '$c2',
            '$c3',
            $s,
            $s_cur,
            $p,
            $p_cur,
            $a,
            $a_cur,
            $bac,
            $bac_diff,
            $eac,
            $eac_diff,
            $s_hours,
            $s_hours_cur,
            $p_hours,
            $p_hours_cur,
            $a_hours,
            $a_hours_cur,
            $bac_hours,
            $bac_hours_diff,
            $eac_hours,
            $eac_hours_diff,
            $pc),";
    if($i==500){
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql,$schema);
        $sql = $insert_sql;
        $i=0;
    }
    $i++;
    $rs->MoveNext();
}
if($i!=500){
    $sql = substr($sql, 0, -1);
    print $sql;
    $junk = dbCall($sql,$schema);
    $sql = $insert_sql;
}

}
function insertCobraCostData( $ship_code, $schema, $rpt_period, $ship_code_wc){

    $stage_table_name   = $rpt_period . "_stage";
    $create_table = checkIfTableExists($schema, $stage_table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema, "template_stage", $stage_table_name);
    }
    deleteShipFromTable($ship_code,$stage_table_name, $schema);

    insertCobraCostDataStage($ship_code, $schema, $stage_table_name, $ship_code_wc);

    $prev_rpt_period = getPreviousRPTPeriod($rpt_period);
    $prev_table_name = $prev_rpt_period . "_cost";

    $cur_table_name   = $rpt_period . "_cost";
    $create_table = checkIfTableExists($schema, $cur_table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema, "template_cost", $cur_table_name);
    }
    deleteShipFromTable($ship_code,$cur_table_name, $schema);
    insertCobraCostDataCur($ship_code, $schema, $stage_table_name, $cur_table_name, $prev_table_name);
}
function loadLCSProgramData($ship_code, $table_name){
    $sql = "
     select
            PROGRAM,
            ctc,
            auw,
            otc,
            cbb,
            fee,
            mr,
            ub,
            eac_best,
            eac_worst,
            acwp,
            acwp_hrs,
            bcwp,
            bcwp_hrs,
            bcws,
            bcws_hrs,
            bac,
            bac_hrs,
            eac,
            eac_hrs,
            estprice
             from
          program
          where program = '$ship_code'
     ";
    $rs = dbCallCobra($sql);
    $schema = "lcs_log";
    $insert_sql = "
        INSERT into $schema.$table_name
            (ship_code,
            ctc,
            auw,
            otc,
            cbb,
            fee,
            mr,
            ub,
            eac_best,
            eac_worst,
            a,
            a_hours,
            p,
            p_hours,
            s,
            s_hours,
            bac,
            bac_h,
            eac,
            eac_h,
            est_price)
          values

 ";
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF)
    {

        $ctc       = formatNumber4decNoComma($rs->fields["ctc"]);
        $auw       = formatNumber4decNoComma($rs->fields["auw"]);
        $otc       = formatNumber4decNoComma($rs->fields["otc"]);
        $cbb       = formatNumber4decNoComma($rs->fields["cbb"]);
        $fee       = formatNumber4decNoComma($rs->fields["fee"]);
        $mr        = formatNumber4decNoComma($rs->fields["mr"]);
        $ub        = formatNumber4decNoComma($rs->fields["ub"]);
        $eac_best  = formatNumber4decNoComma($rs->fields["eac_best"]);
        $eac_worst = formatNumber4decNoComma($rs->fields["eac_worst"]);
        $s         = formatNumber4decNoComma($rs->fields["bcws"]);
        $p         = formatNumber4decNoComma($rs->fields["bcwp"]);
        $a         = formatNumber4decNoComma($rs->fields["acwp"]);
        $bac       = formatNumber4decNoComma($rs->fields["bac"]);
        $eac       = formatNumber4decNoComma($rs->fields["eac"]);
        $s_hours   = formatNumber4decNoComma($rs->fields["bcws_hrs"]);
        $p_hours   = formatNumber4decNoComma($rs->fields["bcwp_hrs"]);
        $a_hours   = formatNumber4decNoComma($rs->fields["acwp_hrs"]);
        $bac_hours = formatNumber4decNoComma($rs->fields["bac_hrs"]);
        $eac_hours = formatNumber4decNoComma($rs->fields["eac_hrs"]);
        $est_price = formatNumber4decNoComma($rs->fields["estprice"]);

        $sql.="(
            $ship_code,
            $ctc,
            $auw,
            $otc,
            $cbb,
            $fee,
            $mr,
            $ub,
            $eac_best,
            $eac_worst,
            $a,
            $a_hours,
            $p,
            $p_hours,
            $s,
            $s_hours,
            $bac,
            $bac_hours,
            $eac,
            $eac_hours,
            $est_price
            ),";
        if($i==500){
            $sql = substr($sql, 0, -1);

            $junk = dbCall($sql,$schema);
            $sql = $insert_sql;
            $i=0;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i!=500){
        $sql = substr($sql, 0, -1);
        print $sql;

        $junk = dbCall($sql,$schema);

        $sql = $insert_sql;
    }
}

/*
 * **************************************
 * *************************************
 * ***************************************
 * Load 201512
 * */

$schema = "cost2";
$ship_code_array = array();

$ship_code_array["0485"] = "04850616";
//$ship_code_array["0483"] = "04830216";

//$ship_code_array["0481"] = "04811215";

/*$ship_code_array["0479"] = "04791215";
$ship_code_array["0477"] = "04771215";
$ship_code_array["0475"] = "04751215";
$ship_code_array["0473"] = "04731215";
$ship_code_array["0471"] = "04711215";
$ship_code_array["0469"] = "04691215";*/

$rpt_period = 201606;
foreach ($ship_code_array as $ship_code=>$ship_code_wc){
    insertCobraCostData($ship_code, $schema, $rpt_period, $ship_code_wc);

}