<?PHP

date_default_timezone_set('Asia/Jakarta');
ini_set("memory_limit","512M");
ini_set('max_execution_time', 0);


session_start();
if (!isset($_SESSION['USERID'])) {
    echo "ANDA HARUS LOGIN ULANG....";
    exit;
}

include("config/koneksimysqli.php");
include "config/fungsi_combo.php";
include "config/fungsi_sql.php";
include("config/common.php");
include "config/fungsi_ubahget_id.php";

$pidgroup=$_SESSION['GROUP'];
$pidjabatan=$_SESSION['JABATANID'];
$puserid=$_SESSION['USERID'];
$now=date("mdYhis");
$tmp01 =" dbtemp.tmptariklhtks01_".$puserid."_$now ";
$tmp02 =" dbtemp.tmptariklhtks02_".$puserid."_$now ";
$tmp03 =" dbtemp.tmptariklhtks03_".$puserid."_$now ";
$tmp04 =" dbtemp.tmptariklhtks04_".$puserid."_$now ";
$pmodule=$_GET['module'];

$pkaryawanid="";
$pdokterid="";
$pbln="";


if ($pmodule=="lihatdataksusr") {
    $pkaryawanid = $_GET['iid']; 
    $pdokterid = $_GET['ind'];
    $pbln = date("Y-m-d");
}else{
    $pkaryawanid = $_POST['cb_karyawan']; 
    $pdokterid = $_POST['e_iddokt'];
    $pbln = $_POST['e_bulan']; 
}


$pbulan = date('Y-m', strtotime($pbln));

$query = "select nama from hrd.karyawan where karyawanid='$pkaryawanid'";
$tampilk=mysqli_query($cnmy, $query);
$rowk=mysqli_fetch_array($tampilk);
$pnamakarywanpl=$rowk['nama'];

$query = "select nama as nama_dokter from hrd.dokter where dokterid='$pdokterid'";
$tampilk=mysqli_query($cnmy, $query);
$rowk=mysqli_fetch_array($tampilk);
$pnamadokter=$rowk['nama_dokter'];

$query = "select srid, dokterid, bulan, iprodid, apttype, idapotik, cn_ks as cn, qty, hna, (qty*hna) as tvalue 
    from hrd.ks1 where srid='$pkaryawanid' AND dokterid='$pdokterid' AND bulan<='$pbulan '";
$query = "CREATE TEMPORARY TABLE $tmp01 ($query)";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }



$query = "ALTER TABLE $tmp01 ADD saldocn DECIMAL(20,2)";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }

$query = "UPDATE $tmp01 SET saldocn=case when IFNULL(cn,0)=0 then 0 else IFNULL(tvalue,0)*(IFNULL(cn,0)/100) end";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }

$query = "UPDATE $tmp01 SET saldocn=case when IFNULL(cn,0)=0 then 0 else (IFNULL(tvalue,0)*0.8) * (IFNULL(cn,0)/100) end WHERE apttype<>'1'";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }



$query = "select a.brid, a.tgl, DATE_FORMAT(tgl,'%Y-%m') as bulan, a.mrid, a.dokterid, a.jumlah, a.jumlah1, a.aktivitas1, a.aktivitas2  
    from hrd.br0 as a JOIN hrd.br_kode as b on a.kode=b.kodeid 
    where a.mrid='$pkaryawanid' AND a.dokterid='$pdokterid' AND b.ks='Y' and IFNULL(a.batal,'')<>'Y' AND 
    IFNULL(a.retur,'')<>'Y' and a.brid not in (select distinct IFNULL(brid,'') from hrd.br0_reject)";
$query = "CREATE TEMPORARY TABLE $tmp02 ($query)";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }

$query = "UPDATE $tmp02 set jumlah=jumlah1 WHERE IFNULL(jumlah1,0)<>0";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }


$query = "select a.*, b.nama as nama_produk, c.nama as nama_apotik 
    from $tmp01 as a 
    left join MKT.iproduk as b on a.iprodid = b.iProdId
    left join hrd.mr_apt as c on a.idapotik=c.idapotik";
$query = "CREATE TEMPORARY TABLE $tmp03 ($query)";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }

$query = "select karyawanid, dokterid, tgl, awal, cn FROM hrd.mrdoktbaru WHERE karyawanid='$pkaryawanid' AND dokterid='$pdokterid'";
$query = "CREATE TEMPORARY TABLE $tmp04 ($query)";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }


$query = "UPDATE $tmp04 as a JOIN (select srid, dokterid, bulan FROM $tmp01 order by srid, dokterid, bulan LIMIT 1) as b on a.karyawanid=b.srid AND a.dokterid=b.dokterid SET a.tgl=CONCAT(b.bulan, '-01') WHERE 
    IFNULL(a.tgl,'')='' OR a.tgl='0000-00-00'";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }



$query = "select bulan from $tmp03 order by bulan LIMIT 1";
$tampilk=mysqli_query($cnmy, $query);
$rowk=mysqli_fetch_array($tampilk);
$pmin_bulan=$rowk['bulan'];
if ($pmin_bulan=="0000-00") $pmin_bulan="";


$query = "INSERT INTO $tmp03 (bulan, nama_produk)
    select DISTINCT bulan, 'ZZINPUTKI' as nama_produk FROM $tmp02 WHERE bulan>='$pmin_bulan' AND bulan NOT IN 
    (select distinct IFNULL(bulan,'') FROM $tmp01)";
mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }

?>


<HTML>
<HEAD>
  <TITLE>Lihat Kartu Status</TITLE>
    <meta http-equiv="Expires" content="Mon, 01 Jan 2030 1:00:00 GMT">
    <meta http-equiv="Pragma" content="no-cache">
    <?php header("Cache-Control: no-cache, must-revalidate"); ?>
    <link rel="shortcut icon" href="images/icon.ico" />
    <style> .str{ mso-number-format:\@; } </style>
    
    <!-- Bootstrap -->
    <link href="vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    
    <!-- Custom Theme Style -->
    <link href="build/css/custom.min.css" rel="stylesheet">
    
    <style>
    @media print
    {    
        .no-print, .no-print *
        {
            display: none !important;
        }
    }
    </style>
</HEAD>
<script src="ks.js">
</script>

<BODY onload="initVar()" style="margin-left:10px; color:#000; background-color:#fff;">
    
    <div class='modal fade' id='myModal' role='dialog' class='no-print'></div>
    <button onclick="topFunction()" id="myBtn" title="Go to top" class='no-print'>Top</button>

    <?PHP

        echo "<b>Kartu Status : $pnamakarywanpl - $pkaryawanid</b><br>";
        echo "<br>Customer : $pnamadokter - $pdokterid<br>";

        echo "<table border='1' cellspacing='0' cellpadding='1'>";
            echo "<tr>";
                echo "<th align='left'><small>Bulan&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</small></th>";
                $header_ = add_space('Apotik',40);
                echo "<th align='left'><small>$header_</small></th>";
                echo "<th><small>Jenis</small></th>";
                $header_ = add_space('Produk',40);
                echo "<th align='left'><small>$header_</small></th>";
                echo "<th align='center'><small>Qty</small></th>";
                echo "<th><small>HNA</small></th>";
                echo "<th><small>Value</small></th>";
                echo "<th align='center'><small>Jumlah</small></th>";
                echo "<th align='center'><small>Total</small></th>";
            echo "</tr>";

            $p_isaldo=0;

            $query = "select awal from hrd.`ks1_saldoawal` WHERE karyawanid='$pkaryawanid' AND dokterid='$pdokterid' AND IFNULL(awal,0)<>0";
            $tampil=mysqli_query($cnmy, $query);
            $ketemu=mysqli_num_rows($tampil);
            if ((INT)$ketemu>0) {
                while ($row=mysqli_fetch_array($tampil)) {
                    //$ptglsldawal=$row['tgl'];
                    $psldawal=$row['awal'];

                    //if ($ptglsldawal=="0000-00" OR $ptglsldawal=="0000-00-00") {
                    //    $ptglsldawal="";
                    //}
                    if (empty($psldawal)) $psldawal=0;

                    $p_isaldo=(DOUBLE)$p_isaldo+(DOUBLE)$psldawal;

                    //$psldawal = number_format($psldawal,0);
                    $psldawal=number_format($psldawal,0,"","");

                    echo "<tr>";
                    echo "<td><small>$pmin_bulan</small></td>";  
                    echo "<td>&nbsp;</td>";
                    echo "<td>&nbsp;</td>";
                    echo "<td><small><b>SA</b></small></td>";
                    echo "<td>&nbsp;</td>";
                    echo "<td>&nbsp;</td>";
                    echo "<td>&nbsp;</td>";
                    echo "<td>&nbsp;</td>";
                    echo "<td align='right'><small><b>$psldawal</b></small></td>";
                    echo "</tr>";

                }
            }else{
                echo "<tr>";
                echo "<td><small>$pmin_bulan</small></td>";  
                echo "<td>&nbsp;</td>";
                echo "<td>&nbsp;</td>";
                echo "<td><small><b>SA</b></small></td>";
                echo "<td>&nbsp;</td>";
                echo "<td>&nbsp;</td>";
                echo "<td>&nbsp;</td>";
                echo "<td>&nbsp;</td>";
                echo "<td align='right'><small><b>0</b></small></td>";
                echo "</tr>";
            }



            //out of range KI
            if (!empty($pmin_bulan)) {
                $query = "select brid, tgl, sum(jumlah) as jumlahki from $tmp02 WHERE bulan<'$pmin_bulan' GROUP BY 1 order by 1,2";
                $tampil0=mysqli_query($cnmy, $query);
                $ketemu0=mysqli_num_rows($tampil0);
                if ((INT)$ketemu0>0) {
                    while ($row0=mysqli_fetch_array($tampil0)) {
                        $pbridki=$row0['brid'];
                        $ptglki=$row0['tgl'];
                        $pjumlahki=$row0['jumlahki'];

                        $ptglki = date('Y-m', strtotime($ptglki));

                        if (empty($pjumlahki)) $pjumlahki=0;

                        $p_isaldo=(DOUBLE)$p_isaldo+(DOUBLE)$pjumlahki;

                        //$pjumlahki = number_format($pjumlahki,0);
                        $pjumlahki=number_format($pjumlahki,0,"","");

                        $pidnoget=encodeString($pbridki);

                        $pbolehlhtketbr=false;
                        if ($pidgroup=="1" OR $pidgroup=="24") {
                            $pbolehlhtketbr=true;
                        }else{
                            if ($pidjabatan=="08" OR $pidjabatan=="20" OR $pidjabatan=="05") {
                                $pbolehlhtketbr=true;
                            }else{
                                $pbolehlhtketbr=false;
                            }
                        }

                        $plihatket="KI";
                        $pketdetail="";
                        if ($pbolehlhtketbr==true) {
                            $plihatket="<a title='' href='#' class='btn btn-info btn-xs' data-toggle='modal' "
                                . "onClick=\"window.open('eksekusi3.php?module=kslihatkslhtzz&brid=$pidnoget&iprint=print',"
                                . "'Ratting','width=500,height=200,left=500,top=100,scrollbars=yes')\"> "
                                . "KI</a>";

                            $pketdetail="<span id='spn_ki' class='no-print'><button type='button' class='btn btn-info btn-xs' data-toggle='modal' "
                                    . " data-target='#myModal' onClick=\"LiatDetailBr('$pbridki')\">Detail</button></span>";
                        }
                                
                        echo "<tr>";
                        echo "<td><small>$ptglki</small></td>";  
                        echo "<td><small><b>KI</b></small></td>";
                        echo "<td><small>$pketdetail</small></td>";
                        echo "<td><small>&nbsp;</small></td>";
                        echo "<td align='right'><small>&nbsp;</small></td>";
                        echo "<td align='right'><small>&nbsp;</small></td>";
                        echo "<td align='right'><small><b>&nbsp;</b></small></td>";
                        echo "<td align='right'><small><b>$pjumlahki</b></small></td>";
                        echo "<td align='right'><small><b>&nbsp;</b></small></td>";
                        echo "</tr>";

                        
                    }
                }
            }


            $ptotalval=0;
            $ptotalsldcn=0;

            $query = "select distinct bulan from $tmp03 order by bulan";
            $tampil2=mysqli_query($cnmy, $query);
            $ketemu2=mysqli_num_rows($tampil2);
            if ((INT)$ketemu2>0) {
                while ($row2=mysqli_fetch_array($tampil2)) {
                    $nbulan=$row2['bulan'];

                    $precawal=1;
                    $plewatks=false;
                    $ptotalval=0;
                    $ptotalsldcn=0;
                    $prec=1;
                    $query = "select * from $tmp03 WHERE bulan='$nbulan' order by bulan, nama_apotik, idapotik, nama_produk, iprodid";
                    $tampil3=mysqli_query($cnmy, $query);
                    $ketemu3=mysqli_num_rows($tampil3);
                    if ((INT)$ketemu3>0) {
                        while ($row3=mysqli_fetch_array($tampil3)) {
                            $pidapotik=$row3['idapotik'];
                            $pnmapotik=$row3['nama_apotik'];
                            $ptypeapotik=$row3['apttype'];

                            $pidprod=$row3['iprodid'];
                            $pnmprod=$row3['nama_produk'];

                            $pqty=$row3['qty'];
                            $phna=$row3['hna'];
                            $pvalue=$row3['tvalue'];
                            $cn=$row3['cn'];
                            $psaldocn=$row3['saldocn'];
                            

                            if (empty($pqty)) $pqty=0;
                            if (empty($phna)) $phna=0;
                            if (empty($pvalue)) $pvalue=0;
                            if (empty($psaldocn)) $psaldocn=0;

                            $p_isaldo=(DOUBLE)$p_isaldo-(DOUBLE)$psaldocn;
                            $psldcnminus=-1*(DOUBLE)$psaldocn;
                            if ((DOUBLE)$psldcnminus==-0) $psldcnminus=0;

                            $ptotalval=(DOUBLE)$ptotalval+(DOUBLE)$pvalue;
                            $ptotalsldcn=(DOUBLE)$ptotalsldcn+(DOUBLE)$psldcnminus;
                            

                            //$pqty = number_format($pqty,0);
                            //$phna = number_format($phna,0);
                            //$pvalue = number_format($pvalue,0);
                            //$psaldocn = number_format($psaldocn,0);

                            $pqty=number_format($pqty,0,"","");
                            $phna=number_format($phna,0,"","");
                            $pvalue=number_format($pvalue,0,"","");
                            $psaldocn=number_format($psaldocn,0,"","");


                            if ($ptypeapotik=="1") {
                                $pnmtypeapt="D";
                            } else {
                                $pnmtypeapt="R";
                            }

                            $nidapt=(INT)$pidapotik;
                            $pnama_dan_idapt="$pnmapotik ($nidapt)";
                            if (empty($pnmapotik)) $pnama_dan_idapt="";


                            $sim="";
                            if ($cn > '20') {
                                $sim = 'A+';
                            } else {
                                if ($cn == '20') {
                                    $sim = 'A';
                                } else {
                                    if ($cn > '15' and $cn < '20') {
                                        $sim = 'B+';
                                    } else {
                                        if ($cn == '15') {
                                            $sim = 'B';
                                        } else {
                                            if ($cn > '10' and $cn < '15') {
                                                $sim = 'C+';
                                            } else {
                                                if ($cn == '10') {
                                                    $sim = 'C';
                                                } else {
                                                    if ($cn > '5' and $cn < '10') {
                                                        $sim = 'D+';
                                                    } else {
                                                        if ($cn == '5') {
                                                            $sim = 'D';
                                                        } else {
                                                            if ($cn < '5') {
                                                                $sim = 'E';
                                                            } else {
                                                            }												
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }


                            if ($pnmprod=="ZZINPUTKI") {

                            }else{
                                $plewatks=true;
                                if ($precawal==1) {
                                    echo "<tr>";
                                    echo "<td><small>$nbulan</small></td>";  
                                    echo "<td>&nbsp;</td>";
                                    echo "<td>&nbsp;</td>";
                                    echo "<td><small><b>&nbsp;</b></small></td>";
                                    echo "<td>&nbsp;</td>";
                                    echo "<td>&nbsp;</td>";
                                    echo "<td>&nbsp;</td>";
                                    echo "<td>&nbsp;</td>";
                                    echo "<td align='right'><b>&nbsp;</b></td>";
                                    echo "</tr>";
                                }
                                $precawal++;

                                echo "<tr>";
                                if ((INT)$prec==1) {
                                    echo "<td><small>SIM = $sim</small></td>";
                                }else{
                                    echo "<td><small>&nbsp;</small></td>";  
                                }
                                echo "<td><small>$pnama_dan_idapt</small></td>";
                                echo "<td><small>$pnmtypeapt</small></td>";
                                echo "<td><small>$pnmprod</small></td>";
                                echo "<td align='right'><small>$pqty</small></td>";
                                echo "<td align='right'><small>$phna</small></td>";
                                echo "<td align='right'><small>$pvalue</small></td>";
                                echo "<td align='right'><small>$psldcnminus</small></td>";
                                echo "<td align='right'>&nbsp;</td>";
                                echo "</tr>";

                                $prec++;
                            }
                        }


                        if ($plewatks==true) {
                            //$ptotalval = number_format($ptotalval,0);
                            //$ptotalsldcn = number_format($ptotalsldcn,0);

                            $ptotalval=number_format($ptotalval,0,"","");
                            $ptotalsldcn=number_format($ptotalsldcn,0,"","");

                            echo "<tr>";
                            echo "<td><small>&nbsp;</small></td>";  
                            echo "<td><small>&nbsp;</small></td>";
                            echo "<td><small>&nbsp;</small></td>";
                            echo "<td><small>&nbsp;</small></td>";
                            echo "<td align='right'><small>&nbsp;</small></td>";
                            echo "<td align='right'><small>&nbsp;</small></td>";
                            echo "<td align='right'><small><b>$ptotalval</b></small></td>";
                            echo "<td align='right'><small><b>$ptotalsldcn</b></small></td>";
                            echo "<td align='right'>&nbsp;</td>";
                            echo "</tr>";
                        }


                        //KI
                        $query = "select tgl, brid, sum(jumlah) as jumlahki from $tmp02 WHERE bulan='$nbulan' GROUP BY 1,2 order by 1";
                        $tampil4=mysqli_query($cnmy, $query);
                        $ketemu4=mysqli_num_rows($tampil4);
                        if ((INT)$ketemu4>0) {
                            while ($row4=mysqli_fetch_array($tampil4)) {
                                $pbridki=$row4['brid'];
                                $ptglki=$row4['tgl'];
                                $pjumlahki=$row4['jumlahki'];

                                $ptglki = date('Y-m', strtotime($ptglki));

                                if (empty($pjumlahki)) $pjumlahki=0;

                                $p_isaldo=(DOUBLE)$p_isaldo+(DOUBLE)$pjumlahki;

                                //$pjumlahki = number_format($pjumlahki,0);
                                $pjumlahki=number_format($pjumlahki,0,"","");
                                
                                $pidnoget=encodeString($pbridki);
                                
                                $pbolehlhtketbr=false;
                                if ($pidgroup=="1" OR $pidgroup=="24") {
                                    $pbolehlhtketbr=true;
                                }else{
                                    if ($pidjabatan=="08" OR $pidjabatan=="20" OR $pidjabatan=="05") {
                                        $pbolehlhtketbr=true;
                                    }else{
                                        $pbolehlhtketbr=false;
                                    }
                                }
                                
                                $plihatket="KI";
                                $pketdetail="";
                                if ($pbolehlhtketbr==true) {
                                    $plihatket="<a title='' href='#' class='btn btn-info btn-xs' data-toggle='modal' "
                                        . "onClick=\"window.open('eksekusi3.php?module=kslihatkslhtzz&brid=$pidnoget&iprint=print',"
                                        . "'Ratting','width=500,height=200,left=500,top=100,scrollbars=yes')\"> "
                                        . "KI</a>";
                                    
                                    $pketdetail="<span id='spn_ki' class='no-print'><button type='button' class='btn btn-info btn-xs' data-toggle='modal' "
                                            . " data-target='#myModal' onClick=\"LiatDetailBr('$pbridki')\">Detail</button></span>";
                                }
                                
                    
                                echo "<tr>";
                                echo "<td><small>$ptglki</small></td>";  
                                echo "<td><small><b>KI</b></small></td>";
                                echo "<td><small>$pketdetail</small></td>";
                                echo "<td><small>&nbsp;</small></td>";
                                echo "<td align='right'><small>&nbsp;</small></td>";
                                echo "<td align='right'><small>&nbsp;</small></td>";
                                echo "<td align='right'><small><b>&nbsp;</b></small></td>";
                                echo "<td align='right'><small><b>$pjumlahki</b></small></td>";
                                echo "<td align='right'><small><b>&nbsp;</b></small></td>";
                                echo "</tr>";

                                
                            }
                        }


                        $psaldoakhir=$p_isaldo;

                        //$psaldoakhir = number_format($psaldoakhir,0);
                        $psaldoakhir=number_format($psaldoakhir,0,"","");

                        echo "<tr>";
                        echo "<td><small>&nbsp;</small></td>";  
                        echo "<td><small>&nbsp;</small></td>";
                        echo "<td><small>&nbsp;</small></td>";
                        echo "<td><small>&nbsp;</small></td>";
                        echo "<td align='right'><small>&nbsp;</small></td>";
                        echo "<td align='right'><small>&nbsp;</small></td>";
                        echo "<td align='right'><small><b>&nbsp;</b></small></td>";
                        echo "<td align='right'><small><b>&nbsp;</b></small></td>";
                        echo "<td align='right'><small><b>$psaldoakhir</b></small></td>";
                        echo "</tr>";



                        //jarak

                        echo "<tr>";
                        echo "<td><small>&nbsp;</small></td>";  
                        echo "<td>&nbsp;</td>"; echo "<td>&nbsp;</td>";
                        echo "<td><small><b>&nbsp;</b></small></td>";
                        echo "<td>&nbsp;</td>";echo "<td>&nbsp;</td>";echo "<td>&nbsp;</td>";echo "<td>&nbsp;</td>";
                        echo "<td align='right'><b>&nbsp;</b></td>";
                        echo "</tr>";
                    }

                }

                if (empty($p_isaldo)) $p_isaldo=0;

                //$p_isaldo = number_format($p_isaldo,0);
                $p_isaldo=number_format($p_isaldo,0,"","");

                echo "<tr>";
                echo "<td><small>&nbsp;</small></td>";  
                echo "<td><small>&nbsp;</small></td>";
                echo "<td><small>&nbsp;</small></td>";
                echo "<td><small>&nbsp;</small></td>";
                echo "<td align='right'><small>&nbsp;</small></td>";
                echo "<td align='right'><small>&nbsp;</small></td>";
                echo "<td align='right'><small><b>&nbsp;</b></small></td>";
                echo "<td align='right'><small><b>Total :</b></small></td>";
                echo "<td align='right'><small><b>$p_isaldo</b></small></td>";
                echo "</tr>";

            }


        echo "</table>";

        echo "<br/>&nbsp;<br/>&nbsp;<br/>&nbsp;";

    ?>

</BODY>

    <!-- jQuery -->
    <script src="vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="vendors/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="build/js/custom.min.js"></script>
    
    <style>
        #myBtn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 30px;
            z-index: 99;
            font-size: 18px;
            border: none;
            outline: none;
            background-color: red;
            color: white;
            cursor: pointer;
            padding: 15px;
            border-radius: 4px;
            opacity: 0.5;
        }

        #myBtn:hover {
            background-color: #555;
        }

    </style>
    <style>
        a {
          color: #000;
          text-decoration: none;
        }
    </style>

    <style>
        #tbltable {
            border-collapse: collapse;
        }
        th {
            font-size : 16px;
            padding:5px;
            background-color: #ccccff;
        }
        tr td {
            font-size : 14px;
        }
        tr td {
            padding : 3px;
        }
        tr:hover {background-color:#f5f5f5;}
        thead tr:hover {background-color:#cccccc;}
    </style>
    
    <script>
        // SCROLL
        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};
        function scrollFunction() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                document.getElementById("myBtn").style.display = "block";
            } else {
                document.getElementById("myBtn").style.display = "none";
            }
        }

        // When the user clicks on the button, scroll to the top of the document
        function topFunction() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        }
        // END SCROLL
    </script>
    
    
    <script>
        function LiatDetailBr(eid){
            $.ajax({
                type:"post",
                url:"module/ks_lihatks/aksi_lihatks_br_mdl.php?module=viewbrdetail",
                data:"uid="+eid,
                success:function(data){
                    $("#myModal").html(data);
                }
            });
        }
    </script>
</HTML>

<?PHP
hapusdata:
    mysqli_query($cnmy, "drop TEMPORARY table $tmp01");
    mysqli_query($cnmy, "drop TEMPORARY table $tmp02");
    mysqli_query($cnmy, "drop TEMPORARY table $tmp03");
    mysqli_query($cnmy, "drop TEMPORARY table $tmp04");
    mysqli_close($cnmy);
?>