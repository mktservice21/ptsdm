<?PHP
session_start();

    if (!isset($_SESSION['USERID'])) {
        echo "ANDA HARUS LOGIN ULANG....";
        exit;
    }
    
    $pidmenu=$_GET['idmenu'];
    
    //ini_set('display_errors', '0');
    date_default_timezone_set('Asia/Jakarta');
    ini_set("memory_limit","512M");
    ini_set('max_execution_time', 0);
    
    
    $ppilihsts = strtoupper($_POST['eket']);
    $mytgl1 = $_POST['uperiode1'];
    $mytgl2 = $_POST['uperiode2'];
    $pkaryawanid = $_POST['ukaryawan'];
    $pstsapv = $_POST['uketapv'];
    
    $_SESSION['PCHSSIVSTS']=$ppilihsts;
    $_SESSION['PCHSSIVTGL1']=$mytgl1;
    $_SESSION['PCHSSIVTGL2']=$mytgl2;
    $_SESSION['PCHSSIVPVBY']=$pkaryawanid;
    
    
    $pbulan1= date("Y-m-01", strtotime($mytgl1));
    $pbulan2= date("Y-m-t", strtotime($mytgl2));
    
    $pidgroup=$_SESSION['GROUP'];
    $userid=$_SESSION['USERID'];
    $now=date("mdYhis");
    $tmp01 =" dbtemp.TMPISIVENVN01_".$userid."_$now ";
    
    
include "../../../config/koneksimysqli.php";


?>
<form method='POST' action='<?PHP echo "?module='pchisivendorprvrfq'&act=input&idmenu=$pidmenu"; ?>' 
      id='d-formv' name='formv' data-parsley-validate class='form-horizontal form-label-left' target="_blank"></form>

<div class='col-md-12 col-sm-12 col-xs-12'>
    <div class='x_panel'>
        
        
        <div class="page-title">
            <h3>
                <?PHP echo "<u>List Data RFQ</u>"; ?>
            </h3>
        </div>
        <div class="clearfix"></div>
        
        <div>
            <input type='button' class='btn btn-dark  btn-xs' id="s-submit" value="Preview" onclick="PreviewData('bukan')">
            <input type='button' class='btn btn-danger  btn-xs' id="s-submit" value="Excel" onclick="PreviewData('excel')">
        </div>
        <div class="clearfix"></div>
        
        <div class='x_content' style="overflow-x:auto; max-height:500px">

            <table id='dttblisivendor' class='table table-striped table-bordered' width='100%'>
                <thead>
                    <tr>
                        <th width='7px'>No</th>
                        <th width='10px'>
                            <input type="checkbox" id="chkbtnbr" value="select" 
                            onClick="SelAllCheckBox('chkbtnbr', 'chkbox_br[]')" />
                        </th>
                        <th width='30px'>Tgl. Input RFQ</th>
                        <th width='30px'>Vendor</th>
                        <th width='30px'>Nama Barang</th>
                        <th width='50px'>Spesifikasi</th>
                        <th width='50px'>Jumlah</th>
                        <th width='50px'>Harga</th>
                        <th width='50px'>Pilih</th>
                        <th width='50px'>PO</th>
                        <th width='50px'>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?PHP
                    $no=1;
                    $pposdh=false;
                    $pbelum=false;
                    $query = "select a.tglinput, c.idpo, a.idpr, a.idpr_d, a.idpr_po, 
                            a.kdsupp, b.NAMA_SUP as nama_sup, b.ALAMAT as alamat, b.TELP as telp, 
                            a.idbarang, a.namabarang, 
                            a.idbarang_d, a.spesifikasi1, a.spesifikasi2, 
                            a.uraian, a.keterangan, 
                            a.jumlah, a.harga, a.aktif, a.userid, c.kdsupp as kdsupp_po 
                            from dbpurchasing.t_pr_transaksi_po as a 
                            LEFT JOIN dbmaster.t_supplier as b on a.kdsupp=b.KDSUPP 
                            LEFT JOIN (select aa.idpo, aa.idpr_po, bb.kdsupp from dbpurchasing.t_po_transaksi_d as aa 
                            JOIN dbpurchasing.t_po_transaksi as bb on aa.idpo=bb.idpo 
                            WHERE IFNULL(bb.stsnonaktif,'')<>'Y') as c on a.idpr_po=c.idpr_po and a.kdsupp=c.kdsupp WHERE 
                            1=1 ";

                    $query .=" AND (LEFT(a.tglinput,10) BETWEEN '$pbulan1' AND '$pbulan2') ";

                    $query .= " order by IFNULL(c.kdsupp,'ZZ'), a.aktif, b.NAMA_SUP";
                    $query = "CREATE TEMPORARY TABLE $tmp01 ($query)";
                    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }

                    $query = "SELECT kdsupp_po FROM $tmp01 WHERE IFNULL(kdsupp_po,'') NOT IN ('') LIMIT 1";
                    $tampilk= mysqli_query($cnmy, $query);
                    $ketemuk= mysqli_num_rows($tampilk);
                    if ((DOUBLE)$ketemuk>0) {
                        $query = "UPDATE $tmp01 SET aktif='N' WHERE IFNULL(kdsupp_po,'')=''";
                        mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }
                    }

                    $query = "SELECT * FROM $tmp01 order by IFNULL(kdsupp_po,'ZZ'), aktif, nama_sup";
                    $tampil= mysqli_query($cnmy, $query);
                    while ($row= mysqli_fetch_array($tampil)) {
                        $pidprpo=$row['idpr_po'];
                        $pidpr=$row['idpr'];
                        $pidpr_d=$row['idpr_d'];
                        $pkdsup=$row['kdsupp'];
                        $pnmsup=$row['nama_sup'];
                        $palamatsup=$row['alamat'];
                        $ptlpsup=$row['telp'];
                        $psts=$row['aktif'];
                        $psudhpo=$row['kdsupp_po'];
                        $pidpo=$row['idpo'];
                        $ptglinput=$row['tglinput'];

                        $pstsaktif="Ya";
                        if ($psts=="N") $pstsaktif="Tidak";

                        if ($psudhpo=="0") $psudhpo="";

                        $pnmbarang=$row['namabarang'];
                        $pspesifikasi=$row['spesifikasi1'];
                        $pketerangan=$row['keterangan'];

                        $pjml=$row['jumlah'];
                        $pharga=$row['harga'];

                        $pjml=number_format($pjml,0,",",",");
                        $pharga=number_format($pharga,0,",",",");
                        
                        $ptglinput= date("d/m/Y", strtotime($ptglinput));
                        
                        $ceklisnya = "<input type='checkbox' value='$pidprpo' name='chkbox_br[]' id='chkbox_br[$pidprpo]' class='cekbr' form='d-formv'>";


                        echo "<tr>";

                        echo "<td nowrap>$no</td>";
                        echo "<td nowrap>$ceklisnya</td>";
                        echo "<td nowrap>$ptglinput</td>";
                        echo "<td nowrap>$pnmsup</td>";
                        echo "<td nowrap>$pnmbarang</td>";
                        echo "<td >$pspesifikasi</td>";
                        echo "<td nowrap align='right'>$pjml</td>";
                        echo "<td nowrap align='right'>$pharga</td>";
                        echo "<td nowrap>$pstsaktif</td>";
                        echo "<td nowrap>$pidpo</td>";
                        echo "<td >$pketerangan</td>";

                        echo "</tr>";


                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        
    </div>
</div>

<style>
    h3 {
        font-size: 15px;
        font-weight: bold;
    }
</style>

<style>
    .divnone {
        display: none;
    }
    #dttblisivendor th {
        font-size: 13px;
    }
    #dttblisivendor td { 
        font-size: 11px;
    }
</style>

<style>

table {
    text-align: left;
    position: relative;
    border-collapse: collapse;
    background-color:#FFFFFF;
}

th {
    background: white;
    position: sticky;
    top: 0;
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
    z-index:1;
}

.th2 {
    background: white;
    position: sticky;
    top: 23;
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
    border-top: 1px solid #000;
}
</style>

<script>
    function PreviewData(pText)  {
        if (pText == "excel") {
            document.getElementById("d-formv").action = "<?PHP echo "eksekusi3.php?module=pchisivendorprvrfq&brid=input&iprint=allprev&ket=excel"; ?>";
            document.getElementById("d-formv").submit();
            return 1;
        }else{
            document.getElementById("d-formv").action = "<?PHP echo "eksekusi3.php?module=pchisivendorprvrfq&brid=input&iprint=allprev&ket=bukan"; ?>";
            document.getElementById("d-formv").submit();
            return 1;
        }
    }
</script>

<?PHP
hapusdata:
    mysqli_query($cnmy, "drop TEMPORARY table $tmp01");
    
    mysqli_close($cnmy);
?>