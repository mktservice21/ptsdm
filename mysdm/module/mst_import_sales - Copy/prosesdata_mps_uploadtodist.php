<?php

    ini_set("memory_limit","10G");
    ini_set('max_execution_time', 0);
    
session_start();
$puser=$_SESSION['IDCARD'];
if (empty($puser)) {
    echo "ANDA HARUS LOGIN ULANG....!!!";
    exit;
}

    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
    
    
    $module=$_GET['module'];
    $act=$_GET['act'];
    $idmenu=$_GET['idmenu'];
    
    $subdist="";
    $cabang='SBY';
    $distributor="0000000025";
    $ptgl=$_POST['ubln'];
    
    $pbulan =  date("Ym", strtotime($ptgl));
    $bulan =  date("Y-m", strtotime($ptgl));
    
    
    if ($distributor!="0000000025") {
        echo "tidak ada data yang diproses...";
        exit;
    }
    
    //ubah juga di _uploaddata
    include "../../config/koneksimysqli_it.php";
    $cnmy=$cnit;
    $dbname = "MKT";
    
    
    $plogit_akses=$_SESSION['PROSESLOGKONEK_IT'];//true or false || status awal true
    if ($plogit_akses==true) {
        include "../../config/koneksimysqli_it.php";
    }
    
    

    echo "$distributor ~ $cabang ~ $bulan<br><br>";

    $totalproduk=0;
    // customer
    $qryproduk="
        SELECT DISTINCT 
        kode AS BRGID,`Nama Barang` AS NAMA,Satuan AS satuan,harga as hna
        FROM $dbname.import_mulyaraya 
        WHERE left(tanggal,7) = '$bulan'
    ";
    
    $tampil_pr= mysqli_query($cnmy, $qryproduk);
    while ($data1= mysqli_fetch_array($tampil_pr)) {
        
        $brgid=$data1['BRGID'];
        $namaproduk=mysqli_real_escape_string($cnmy, $data1['NAMA']);
        $satuan=mysqli_real_escape_string($cnmy, $data1['satuan']);
        $hna=$data1['hna'];
        $cekproduk=mysqli_fetch_array(mysqli_query($cnmy, "SELECT COUNT(eprodid) FROM MKT.eproduk WHERE distid='$distributor' AND eprodid='$brgid'"));
        $cekproduk=$cekproduk[0];
        
        if ($cekproduk<1){
            mysqli_query($cnmy, "
                INSERT INTO MKT.eproduk(distid,eprodid,nama,satuan,hna,aktif,oldflag) 
                    VALUES('$distributor','$brgid','$namaproduk','$satuan',$hna,'Y','Y')
            ");
            echo "berhasil input produk baru -> $namaproduk - $brgid - $hna <br>";
            $totalproduk=$totalproduk+1;
            
            //IT
            if ($plogit_akses==true) {
                mysqli_query($cnit, "
                    INSERT INTO MKT.eproduk(distid,eprodid,nama,satuan,hna,aktif,oldflag) 
                        VALUES('$distributor','$brgid','$namaproduk','$satuan',$hna,'Y','Y')
                ");
            }
            //END IT
            
        }
        
    }    


    echo "Total produk baru yg berhasil diinput: $totalproduk<br><hr><br>";
    
    
    $totalcust=0;
    // customer
    $qrycust="
        SELECT DISTINCT CASE WHEN cust LIKE '%.0' THEN LEFT(cust,4) ELSE cust END CUSTID,`nama customer` CUSTNM 
        FROM $dbname.import_mulyaraya
        WHERE left(tanggal,7) = '$bulan'
        ";
    
    $tampil_cu= mysqli_query($cnmy, $qrycust);
    while ($data1= mysqli_fetch_array($tampil_cu)) {
        
        $ecust=$data1['CUSTID'];
        $enama=mysqli_real_escape_string($cnmy, $data1['CUSTNM']);
        $cekcust=mysqli_fetch_array(mysqli_query($cnmy, "
        select count(distid) from MKT.ecust where distid='$distributor' and cabangid='$cabang' and ecustid='$ecust' and ecustid NOT LIKE '%.0' and nama='$enama'"));
        $cekcust1=$cekcust[0];
        if ($cekcust1<1){
            mysqli_query($cnmy, "
                insert into MKT.ecust(distid,cabangid,ecustid,nama,oldflag,aktif,subdist) 
                values('$distributor','$cabang','$ecust','$enama','Y','Y','$subdist')
                ");
            echo "berhasil input cust baru -> $cabang - $ecust - $enama<br>";
            $totalcust=$totalcust+1;
            
            //IT
            if ($plogit_akses==true) {
                mysqli_query($cnit, "
                    insert into MKT.ecust(distid,cabangid,ecustid,nama,oldflag,aktif,subdist) 
                    values('$distributor','$cabang','$ecust','$enama','Y','Y','$subdist')
                    ");
            }
            //END IT
            
        }
          
    }

    echo "Total Customer baru yg berhasil diinput: $totalcust<br><hr><br>";

    
    // sales
    $totalsalesqty=0;
    $totalsalessum=0;
    mysqli_query($cnmy, "DELETE FROM $dbname.salesmps WHERE left(tgljual,7)='$bulan' AND cabangid='$cabang'");
    
    //IT
    if ($plogit_akses==true) {
        mysqli_query($cnit, "DELETE FROM $dbname.salesmps WHERE left(tgljual,7)='$bulan' AND cabangid='$cabang'");
    }
    //END IT
    
    $qrysales="
        SELECT RIGHT(cust,4) CUSTID,tanggal TGLJUAL,`no.faktur` NOJUAL,kode BRGID,kwantum QBELI,harga HARGA0
        FROM $dbname.import_mulyaraya 
        WHERE left(tanggal,7) = '$bulan'
    ";
    $tampil_sl= mysqli_query($cnmy, $qrysales);
    while ($data1= mysqli_fetch_array($tampil_sl)) {
        
        $custid=$data1['CUSTID'];
        $nojual=$data1['NOJUAL'];
        $brgid=$data1['BRGID'];
        $tgljual=$data1['TGLJUAL'];
        $harga=$data1['HARGA0'];
        $qbeli=$data1['QBELI'];
        $totale=$harga*$qbeli;
        $tabel="$dbname.salesmps";

        //$tahun=substr($nojual,0,4);
        //$bulan=substr($nojual,4,2);
        //$tgl=substr($nojual,6,2);

        //$tanggaljual=$tahun."-".$bulan."-".$tgl;
        $insert=mysqli_query($cnmy, "
            insert into $tabel(cabangid,custid,tgljual,brgid,harga,qbeli,fakturid) 
            values('$cabang',LEFT('$custid',4),'$tgljual','$brgid','$harga','$qbeli','$nojual')
        ");

        if ($insert){
            $totalsalesqty=$totalsalesqty+1;
            $totalsalessum=$totalsalessum+$totale;
        }
        
        //IT
        if ($plogit_akses==true) {
            $insert=mysqli_query($cnit, "
                insert into $tabel(cabangid,custid,tgljual,brgid,harga,qbeli,fakturid) 
                values('$cabang',LEFT('$custid',4),'$tgljual','$brgid','$harga','$qbeli','$nojual')
            ");
        }
        //END IT
        
        
    }
    
    
    
    echo "Total penjualan yg berhasil diinput: $totalsalessum , dengan jumlah no faktur sebanyak $totalsalesqty.<br>Sekian dan terimakasih<br>
    <hr>";
    // mysqli_query($cnmy, "drop table combieth");

    
    mysqli_query($cnmy, "DELETE FROM $dbname.import_mulyaraya");
    
    //IT
    if ($plogit_akses==true) {
        mysqli_query($cnit, "DELETE FROM $dbname.import_mulyaraya");
    }
    //END IT
    
    
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    
    echo "<br/>Selesai dalam ".$total_time." detik<br/>&nbsp;<br/>&nbsp;";
    
    mysqli_close($cnmy);
    
    //IT
    if ($plogit_akses==true) {
        mysqli_close($cnit);
    }
    
?>
    
    
    