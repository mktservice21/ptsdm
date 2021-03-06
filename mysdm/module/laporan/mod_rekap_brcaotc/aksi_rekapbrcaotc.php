<?PHP
    session_start();
    if ($_GET['ket']=="excel") {
        // Fungsi header dengan mengirimkan raw data excel
        header("Content-type: application/vnd-ms-excel");
        // Mendefinisikan nama file ekspor "hasil-export.xls"
        header("Content-Disposition: attachment; filename=REKAP CASH ADVANCE OTC.xls");
    }
    
    include("config/koneksimysqli.php");
    include("config/common.php");
    $cnit=$cnmy;
?>
<html>
<head>
    <title>REKAP CASH ADVANCE OTC</title>
<?PHP if ($_GET['ket']!="excel") { ?>
    <meta http-equiv="Expires" content="Mon, 01 Jan 2007 1:00:00 GMT">
    <meta http-equiv="Pragma" content="no-cache">
    <link rel="shortcut icon" href="images/icon.ico" />
    <link href="css/laporanbaru.css" rel="stylesheet">
    <?php header("Cache-Control: no-cache, must-revalidate"); ?>
<?PHP } ?>
</head>

<body>
<?php
    $tglnow = date("d/m/Y");
    $tglini = date("d F Y");
    $tgl01 = $_POST['bulan1'];
    $periode1 = date("Y-m", strtotime($tgl01));
    $pperpilih01 = date("Y-m-01", strtotime($tgl01));
    $fperiode = " AND date_format(br.periode,'%Y-%m') ='$periode1' ";
    $fperiode2 = " AND ( (DATE_FORMAT(br.periode1, '%Y-%m') = '$periode1') OR (DATE_FORMAT(br.periode2, '%Y-%m') = '$periode1') ) ";
    $per1 = date("F Y", strtotime($tgl01));
    $pbulan = date("F", strtotime($tgl01));
    
    $now=date("mdYhis");
    $tmp01 =" dbtemp.DTBRROTCPCA01_".$_SESSION['IDCARD']."_$now ";
    $tmp02 =" dbtemp.DTBRROTCPCA02_".$_SESSION['IDCARD']."_$now ";
    $tmp03 =" dbtemp.DTBRROTCPCA03_".$_SESSION['IDCARD']."_$now ";
    $tmp04 =" dbtemp.DTBRROTCPCA04_".$_SESSION['IDCARD']."_$now ";
    
    
    
    $query = "SELECT
	br.idca,
	br.periode,
	br.karyawanid,
        br.karyawanid as karyawanid_asli,
	br.icabangid,
	br.areaid,
	br.jumlah,
	br.keterangan, br.divisi, br.ikdkry_kontrak, br.nama_karyawan, br.atasan1, br.atasan2, br.atasan3, br.atasan4 
        FROM dbmaster.t_ca0 AS br WHERE br.jenis_ca='lk' AND br.stsnonaktif <> 'Y' AND br.divisi='OTC' $fperiode";
    
    $query = "CREATE TEMPORARY TABLE $tmp01 ($query)";
    mysqli_query($cnmy, $query);
    $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    $query = "UPDATE $tmp01 SET karyawanid=ikdkry_kontrak WHERE karyawanid='0000002200'";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    $query = "SELECT
	br.idrutin,
	br.bulan,
	br.karyawanid,
	br.karyawanid as karyawanid_asli,
	br.icabangid,
	br.areaid,
	br.jumlah,
	br.keterangan, br.divisi, br.ikdkry_kontrak, br.nama_karyawan, br.atasan1, br.atasan2, br.atasan3, br.atasan4
        FROM dbmaster.t_brrutin0 AS br WHERE br.kode=2 AND IFNULL(br.stsnonaktif,'') <> 'Y' AND br.divisi='OTC' $fperiode2";
    $query = "CREATE TEMPORARY TABLE $tmp02 ($query)";
    mysqli_query($cnmy, $query);
    $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    
    $query = "UPDATE $tmp02 SET karyawanid=ikdkry_kontrak WHERE karyawanid='0000002200'";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    
    $query = "CREATE TEMPORARY TABLE $tmp03 "
            . "(select DISTINCT karyawanid, karyawanid_asli, nama_karyawan, ikdkry_kontrak, divisi, icabangid, areaid, atasan1, atasan2, atasan3, atasan4 from $tmp01)";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    $query = "INSERT INTO $tmp03 (karyawanid, karyawanid_asli, nama_karyawan, ikdkry_kontrak, divisi, icabangid, areaid, atasan1, atasan2, atasan3, atasan4) "
            . " (select DISTINCT karyawanid, karyawanid_asli, nama_karyawan, ikdkry_kontrak, divisi, icabangid, areaid, atasan1, atasan2, atasan3, atasan4 "
            . " from $tmp02 WHERE karyawanid NOT IN (select distinct IFNULL(karyawanid,'') FROM $tmp01))";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    
    $query = "INSERT INTO $tmp03 (karyawanid, karyawanid_asli, nama_karyawan, divisi, icabangid, areaid, atasan1, atasan2, atasan3, atasan4) "
            . " (select DISTINCT karyawanId, karyawanId as karyawanid_asli, '' as nama_karyawan, divisiId, iCabangId, areaId, "
            . " atasanId as atasan1, atasanId as atasan2, atasanId as atasan3, atasanId as atasan4 "
            . " from dbmaster.t_karyawan_posisi WHERE karyawanid NOT IN (select distinct IFNULL(karyawanid,'') FROM $tmp01) AND "
            . " karyawanid NOT IN (select distinct IFNULL(karyawanid,'') FROM $tmp02) AND "
            . " IFNULL(rutin_chc,'')='Y' AND IFNULL(aktif,'')<>'N' )";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    
    $query = "select DISTINCT a.karyawanid, karyawanid_asli, nama_karyawan, c.nama, a.divisi, "
            . " a.icabangid, d.nama as nama_cabang, a.areaid, e.nama as nama_area, "
            . " a.atasan1, a.atasan2, a.atasan3, a.atasan4, "
            . " b.b_norek "
            . " from $tmp03 as a LEFT JOIN "
            . " dbmaster.t_karyawan_posisi as b on a.karyawanid=b.karyawanId "
            . " LEFT JOIN hrd.karyawan as c on a.karyawanid=c.karyawanId "
            . " LEFT JOIN mkt.icabang_o as d on a.icabangid=d.icabangid_o "
            . " LEFT JOIN mkt.iarea_o as e on a.icabangid=e.icabangid_o AND a.areaid=e.areaid_o";
    $query = "CREATE TEMPORARY TABLE $tmp04 ($query)";
    mysqli_query($cnmy, $query);
    $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    
    $query = "UPDATE $tmp04 SET nama=nama_karyawan WHERE karyawanid_asli='0000002200'";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    
    $query = "ALTER TABLE $tmp04 ADD COLUMN CA1 DECIMAL(20,2), ADD COLUMN LK1 DECIMAL(20,2), "
            . " ADD COLUMN SALDO DECIMAL(20,2), ADD COLUMN LK2 DECIMAL(20,2), ADD COLUMN CA2 DECIMAL(20,2), "
            . " ADD COLUMN CAKIRIM DECIMAL(20,2)";
    mysqli_query($cnmy, $query);
    $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    
    $query = "UPDATE $tmp04 as a JOIN (select karyawanid, sum(jumlah) as jumlah FROM $tmp01 GROUP BY 1) as b "
            . " on a.karyawanid=b.karyawanid SET a.CA1=b.jumlah";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    $query = "UPDATE $tmp04 as a JOIN (select karyawanid, sum(jumlah) as jumlah FROM $tmp02 GROUP BY 1) as b "
            . " on a.karyawanid=b.karyawanid SET a.LK1=b.jumlah";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    
    $query = "UPDATE $tmp04 set SALDO=ifnull(IFNULL(CA1,0)-IFNULL(LK1,0),0), LK2=ifnull(IFNULL(LK1,0)-IFNULL(CA1,0),0)";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    $query = "UPDATE $tmp04 set CAKIRIM=ifnull(LK2,0)+ifnull(CA2,0)";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    mysqli_query($cnmy, "drop temporary table IF EXISTS $tmp01");
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
    
    $query = "select * from $tmp04";
    $query = "CREATE TEMPORARY TABLE $tmp01 ($query)";
    mysqli_query($cnmy, $query);
    $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
    
goto kesiniaja;
// =======================================================    
    
    $query = "select k.karyawanId karyawanid, k.nama, b.areaid, b.icabangid, o.nama nama_area, CAST(0  AS DECIMAL(30,2)) CA1 
        , CAST(0  AS DECIMAL(30,2)) LK1, CAST(0  AS DECIMAL(30,2)) SALDO, CAST(0  AS DECIMAL(30,2)) LK2, CAST(0  AS DECIMAL(30,2)) CA2
        , CAST(0  AS DECIMAL(30,2)) CAKIRIM 
        , CAST(''  AS char(100)) KET from hrd.karyawan k JOIN dbmaster.t_karyawan_posisi b on k.karyawanId=b.karyawanId
        LEFT JOIN MKT.iarea_o o on b.areaId=o.areaid_o and b.icabangid=o.icabangid_o
        WHERE k.karyawanId not in (select DISTINCT karyawanId from dbmaster.t_karyawanadmin) and b.divisiId='OTC' and b.aktif='Y' AND k.karyawanId NOT IN ('0000002200', '0000001272')";
    $query = "create temporary table $tmp01 ($query)";
    mysqli_query($cnit, $query);
    
    $query = "INSERT INTO $tmp01 (karyawanId, nama, icabangid, areaid, nama_area)"
            . "select a.id, a.nama, a.icabangid_o, a.areaid_o, b.nama from dbmaster.t_karyawan_kontrak a JOIN MKT.iarea_o b on a.areaid_o=b.areaid_o AND a.icabangid_o=b.icabangid_o";
    mysqli_query($cnit, $query);
	
	
    $query = "SELECT
	br.idca,
	br.periode,
	br.karyawanid,
	br.icabangid,
	br.areaid,
	br.jumlah,
	br.keterangan, br.divisi, br.nama_karyawan, br.atasan1, br.atasan2, br.atasan3, br.atasan4 
        FROM dbmaster.t_ca0 AS br WHERE br.jenis_ca='lk' AND br.stsnonaktif <> 'Y' AND br.divisi='OTC' $fperiode";
    
    $query = "create temporary table $tmp02 ($query)"; 
    mysqli_query($cnit, $query);
    
    $query = "update $tmp02 a JOIN dbmaster.t_karyawan_kontrak b ON a.nama_karyawan=b.nama AND"
            . " a.atasan1=b.atasan1 AND a.atasan2=b.atasan2 AND a.atasan3=b.atasan3 AND a.atasan4=b.atasan4 "
            . " SET a.karyawanid=b.id WHERE a.karyawanid='0000002200'";
    mysqli_query($cnit, $query);
    
    $query = "SELECT
	br.idrutin,
	br.bulan,
	br.karyawanid,
	br.icabangid,
	br.areaid,
	br.jumlah,
	br.keterangan, br.divisi, br.nama_karyawan, br.atasan1, br.atasan2, br.atasan3, br.atasan4
        FROM dbmaster.t_brrutin0 AS br WHERE br.kode=2 AND br.stsnonaktif <> 'Y' AND br.divisi='OTC' $fperiode2";
    
    $query = "create temporary table $tmp03 ($query)"; 
    mysqli_query($cnit, $query);
    
    
    $query = "update $tmp03 a JOIN dbmaster.t_karyawan_kontrak b ON a.nama_karyawan=b.nama AND"
            . " a.atasan1=b.atasan1 AND a.atasan2=b.atasan2 AND a.atasan3=b.atasan3 AND a.atasan4=b.atasan4 "
            . " SET a.karyawanid=b.id WHERE a.karyawanid='0000002200'";
    mysqli_query($cnit, $query);
    
    
    $query = "UPDATE $tmp01 set nama_area=ifnull((select o.nama from MKT.iarea_o o where o.icabangid_o=$tmp01.icabangid AND o.areaid_o=$tmp01.areaid),'')";
    mysqli_query($cnit, $query);
    
    $query = "UPDATE $tmp01 set CA1=ifnull((select sum(jumlah) from  $tmp02 where $tmp02.karyawanid=$tmp01.karyawanId),0)";
    mysqli_query($cnit, $query);
    
    $query = "UPDATE $tmp01 set LK1=ifnull((select sum(jumlah) from  $tmp03 where $tmp03.karyawanid=$tmp01.karyawanId),0)";
    mysqli_query($cnit, $query);
    
    $query = "UPDATE $tmp01 set SALDO=ifnull(CA1-LK1,0), LK2=ifnull(LK1-CA1,0)";
    mysqli_query($cnit, $query);
    
    $query = "UPDATE $tmp01 set CA2=.....";
    ////mysqli_query($cnit, $query);
    
    $query = "UPDATE $tmp01 set CAKIRIM=ifnull(LK2,0)+ifnull(CA2,0)";
    mysqli_query($cnit, $query);
    
    
    ////mysqli_query($cnit, "drop temporary table $tmp01");
    ////mysqli_query($cnit, "drop temporary table $tmp02");
    ////exit;
    
//===================================================================



    
kesiniaja:
        
?>

    <div id="kotakjudul">
        <div id="isikiri">
            <table class='tjudul' width='100%'>
                <tr><td width="150px"><b>PT SDM</b></td><td></td></tr>
                <tr><td width="180px"><b>Rekap Cash Advance OTC </b></td><td><?PHP echo ""; ?></td></tr>
                <tr><td width="150px"><b>Tanggal </b></td><td><?PHP echo "$tglini"; ?></td></tr>
            </table>
        </div>
        <div id="isikanan">
            
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
    
        <table id='datatable2' class='table table-striped table-bordered example_2' border="1px solid black">
            <thead>
                <tr style='background-color:#cccccc; font-size: 13px;'>
                <th align="center">No</th>
                <th align="center">Nama</th>
                <th align="center">Daerah</th>
                <th align="center">CA <?PHP echo $pbulan; ?> yg harus dipertgjwbkan</th>
                <th align="center">Biaya Luar Kota <?PHP echo $pbulan; ?></th>
                <th align="center">Saldo <?PHP echo $pbulan; ?></th>
                <th align="center"></th>
                <th align="center">Kelebihan Biaya LK Bulan <?PHP echo $pbulan; ?></th>
                <th align="center">Permintaan CA Bulan <?PHP echo $pbulan; ?></th>
                <th align="center">CA yang dikirim</th>
            </thead>
            <tbody>
                <?PHP
                    $no=1;
                    $total=0;
                    $totlk1=0;
                    $totalsaldo=0;
                    $totlk2=0;
                    $totca2=0;
                    $totlkirim=0;
                    $query = "select * from $tmp01 order by nama, karyawanid";
                    $result = mysqli_query($cnit, $query);
                    $records = mysqli_num_rows($result);
                    $row = mysqli_fetch_array($result);
                    
                    if ($records) {
                        $reco = 1;
                        while ($reco <= $records) {
                            $noid=$row['karyawanid'];
                            $nama=$row['nama'];
                            $area=$row['nama_area'];
                            $jumlahca1=number_format($row['CA1'],0,",",",");
                            $jumlahlk1=number_format($row['LK1'],0,",",",");
                            
                            $psaldo=number_format($row['SALDO'],0,",",",");
                            $plk2=number_format($row['LK2'],0,",",",");
                            
                            $pca2=number_format($row['CA2'],0,",",",");
                            $pcakirim=number_format($row['CAKIRIM'],0,",",",");
                            
                            $total = $total + $row['CA1'];
                            $totlk1 = $totlk1 + $row['LK1'];
                            $totalsaldo = $totalsaldo + $row['SALDO'];
                            $totlk2 = $totlk2 + $row['LK2'];
                            $totca2 = $totca2 + $row['CA2'];
                            $totlkirim = $totlkirim + $row['CAKIRIM'];
                            
                            echo "<tr>";
                            echo "<td>$no</td>";
                            echo "<td>$nama</td>";
                            echo "<td>$area</td>";
                            echo "<td align='right'>$jumlahca1</td>";
                            echo "<td align='right'>$jumlahlk1</td>";
                            echo "<td align='right'>$psaldo</td>";
                            echo "<td align='right'>&nbsp; &nbsp; &nbsp; &nbsp; </td>";
                            echo "<td align='right'>$plk2</td>";
                            echo "<td align='right'>$pca2</td>";
                            echo "<td align='right'>$pcakirim</td>";
                            echo "</tr>";
                            
                            $no++;
                            $row = mysqli_fetch_array($result);
                            $reco++;  
                            
                        }
                        $total=number_format($total,0,",",",");
                        $totlk1=number_format($totlk1,0,",",",");
                        $totalsaldo=number_format($totalsaldo,0,",",",");
                        $totlk2=number_format($totlk2,0,",",",");
                        $totca2=number_format($totca2,0,",",",");
                        $totlkirim=number_format($totlkirim,0,",",",");
                        //TOTAL
                        echo "<tr>";
                        echo "<td colspan=3 align='right'><b>Total : </b></td>";
                        echo "<td align='right'><b>$total</b></td>";
                        echo "<td align='right'><b>$totlk1</b></td>";
                        echo "<td align='right'><b>$totalsaldo</b></td>";
                        echo "<td align='right'><b>&nbsp; &nbsp; </b></td>";
                        echo "<td align='right'><b>$totlk2</b></td>";
                        echo "<td align='right'><b>$totca2</b></td>";
                        echo "<td align='right'><b>$totlkirim</b></td>";
                        echo "</tr>";
                    }
                    
                    
                ?>
            </tbody>
        </table>
        
        <br/>&nbsp;<br/>&nbsp;
</body>
</html>
<?PHP
hapusdata:
    mysqli_query($cnit, "drop temporary table IF EXISTS $tmp01");
    mysqli_query($cnit, "drop temporary table IF EXISTS $tmp02");
    mysqli_query($cnit, "drop temporary table IF EXISTS $tmp03");
    mysqli_query($cnit, "drop temporary table IF EXISTS $tmp04");
    mysqli_close($cnmy);
    //mysqli_close($cnit);
?>