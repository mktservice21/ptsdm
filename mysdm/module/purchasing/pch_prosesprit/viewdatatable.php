<?php
session_start();


    if (!isset($_SESSION['USERID'])) {
        echo "ANDA HARUS LOGIN ULANG....";
        exit;
    }
    
    include "../../../config/koneksimysqli.php";
    
    //ini_set('display_errors', '0');
    date_default_timezone_set('Asia/Jakarta');
    ini_set("memory_limit","512M");
    ini_set('max_execution_time', 0);

    $pmodule=$_GET['module'];
    $pact=$_GET['act'];
    $pidmenu=$_GET['idmenu'];
    
    
    $ppilihsts = strtoupper($_POST['eket']);
    $mytgl1 = $_POST['uperiode1'];
    $mytgl2 = $_POST['uperiode2'];
    $pkaryawanid = $_POST['ukaryawan'];
    $pstsapv = $_POST['uketapv'];
    
    
    $_SESSION['PCHPRITSTS']=$ppilihsts;
    $_SESSION['PCHPRITTGL1']=$mytgl1;
    $_SESSION['PCHPRITTGL2']=$mytgl2;
    $_SESSION['PCHPRITPVBY']=$pkaryawanid;

    $pbulan1= date("Y-m-01", strtotime($mytgl1));
    $pbulan2= date("Y-m-t", strtotime($mytgl2));
    
    
    if (empty($pkaryawanid)) {
        echo "Anda tidak berhak proses...";
        goto hapusdata;
    }

    $pidcard=$_SESSION['IDCARD'];
    $pidgroup=$_SESSION['GROUP'];
    $userid=$_SESSION['USERID'];
    $now=date("mdYhis");
    $tmp01 =" dbtemp.tmpprositpr01_".$userid."_$now ";
    $tmp02 =" dbtemp.tmpprositpr02_".$userid."_$now ";
    $tmp03 =" dbtemp.tmpprositpr03_".$userid."_$now ";

    $query = "select a.userid as useridinputdetail, h.nama as nmuserinput, b.pengajuan, b.idtipe, g.nama_tipe, a.idpr, 
        b.tglinput, b.tanggal, b.karyawanid, c.nama as nama_karyawan, 
        b.jabatanid, b.divisi, b.icabangid, d.nama as nama_cabang, b.areaid, e.nama nama_area, 
        b.aktivitas, b.userid, f.nama as nama_user,  
        a.idpr_d, a.idbarang, a.namabarang, a.idbarang_d, a.spesifikasi1, 
        a.spesifikasi2, a.uraian, a.keterangan, a.jumlah as jml, a.harga as rp_pr,
        b.atasan1, b.tgl_atasan1, 
        b.atasan2, b.tgl_atasan2, 
        b.atasan3, b.tgl_atasan3,
        b.atasan4, b.tgl_atasan4,
        b.atasan5, b.tgl_atasan5, 
        b.validate1, b.tgl_validate1, 
        b.validate2, b.tgl_validate2 
        from dbpurchasing.t_pr_transaksi_d as a JOIN dbpurchasing.t_pr_transaksi as b on a.idpr=b.idpr 
        JOIN hrd.karyawan c on b.karyawanid=c.karyawanid
        LEFT JOIN MKT.icabang as d on b.icabangid=d.iCabangId
        LEFT JOIN MKT.iarea as e on b.icabangid=e.iCabangId and b.areaid=e.areaid 
        LEFT JOIN hrd.karyawan as f on b.userid=f.karyawanId 
        LEFT JOIN dbpurchasing.t_pr_tipe as g on b.idtipe=g.idtipe 
        LEFT JOIN hrd.karyawan as h on a.userid=h.karyawanId 
        WHERE 1=1 AND IFNULL(pilihpo,'') IN ('Y') ";
    $query .=" AND IFNULL(stsnonaktif,'')<>'Y' AND b.tanggal BETWEEN '$pbulan1' AND '$pbulan2' ";
    if ($ppilihsts=="UNAPPROVE") {
        $query .= " AND (IFNULL(b.tgl_validate1,'')<>'' AND IFNULL(b.tgl_validate1,'0000-00-00 00:00:00')<>'0000-00-00 00:00:00') ";
        //$query .=" AND a.idpr_d IN (select distinct IFNULL(idpr_d,'') from dbpurchasing.t_pr_transaksi_po WHERE IFNULL(aktif,'')='Y') ";
    }else{
        $query .= " AND (IFNULL(b.tgl_validate1,'')='' OR IFNULL(b.tgl_validate1,'0000-00-00 00:00:00')='0000-00-00 00:00:00') ";
    }
    $query .=" AND b.idtipe = '102' ";
    $query = "CREATE TEMPORARY TABLE $tmp01 ($query)";
    mysqli_query($cnmy, $query);
    $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo "$erropesan"; goto hapusdata; }

    
    
    $query = "ALTER table $tmp01 ADD COLUMN sudahapprove varchar(1), ADD COLUMN sudahisivendor varchar(1)"; 
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }
    
    if ($ppilihsts=="APPROVE") {
        $query = "UPDATE $tmp01 SET sudahapprove='Y' WHERE IFNULL(tgl_atasan4,'')<>'' AND IFNULL(tgl_atasan4,'0000-00-00 00:00:00')<>'0000-00-00 00:00:00'";
        mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }
    }
    
    $query = "UPDATE $tmp01 as a JOIN dbpurchasing.t_pr_transaksi_po as b on a.idpr=b.idpr SET a.sudahisivendor='Y' WHERE IFNULL(b.aktif,'')='Y'";
    mysqli_query($cnmy, $query); $erropesan = mysqli_error($cnmy); if (!empty($erropesan)) { echo $erropesan; goto hapusdata; }
    
?>

<form method='POST' action='<?PHP echo "?module='$pmodule'&act=$pact&idmenu=$pidmenu"; ?>' 
      id='d-form2' name='form2' data-parsley-validate class='form-horizontal form-label-left'>

    <div class='x_content' style="overflow-x:auto; max-height: 400px;">

        <?PHP
            $pchkall = "<input type='checkbox' id='chkbtnbr' name='chkbtnbr' value='deselect' onClick=\"SelAllCheckBox('chkbtnbr', 'chkbox_br[]')\" checked/>";
        ?>

        <table id='dttblisivendor' class='table table-striped table-bordered' width='100%'>
            <thead>
                <tr>
                    <th width='7px'>No</th>
                    <th width='20px'>
                        <input type="checkbox" id="chkbtnbr" value="select" 
                        onClick="SelAllCheckBox('chkbtnbr', 'chkbox_br[]')" />
                    </th>
                    <th width='20px'>&nbsp;</th>
                    <th width='20px'>&nbsp;</th>
                    <th width='20px'>ID</th>
                    <th width='30px'>Tanggal</th>
                    <th width='30px'>Yg Mengajukan</th>
                    <th width='30px'>Nama Barang</th>
                    <th width='50px'>Spesifikasi</th>
                    <th width='50px'>Keterangan</th>
                    <th width='50px'>Jumlah</th>
                    <th width='50px'>Harga</th>
                    <th width='20px'>Tipe</th>
                    <th width='20px'>Status</th>
                    <th width='20px'>Edit By</th>
                </tr>
            </thead>
            <tbody>
                <?PHP
                $no=1; $pnomornya="";
                $query = "select distinct idpr from $tmp01 order by IFNULL(sudahapprove,'ZZ'), idpr asc";
                $tampil= mysqli_query($cnmy, $query);
                while ($row= mysqli_fetch_array($tampil)) {
                    $pidpr=$row['idpr'];
                    $pbelumlewat=false;
                    $pnomornya=$no;
                    
                    $npmdl="pchpurchasereq";

                    $pprint="<a title='Detail / Print' href='#' class='btn btn-dark btn-xs' data-toggle='modal' "
                        . "onClick=\"window.open('eksekusi3.php?module=$npmdl&brid=$pidpr&iprint=print',"
                        . "'Ratting','width=700,height=500,left=500,top=100,scrollbars=yes,toolbar=yes,status=1,pagescrool=yes')\"> "
                        . "$pidpr</a>";
                        
                    $ceklisnya = "<input type='checkbox' value='$pidpr' name='chkbox_br[]' id='chkbox_br[$pidpr]' class='cekbr'>";
                    $padaapprove="approve"; $purutannomor=1;
                    
                    $query = "select * from $tmp01 WHERE idpr='$pidpr' order by idpr asc, idpr_d";
                    $tampil1= mysqli_query($cnmy, $query);
                    while ($row1= mysqli_fetch_array($tampil1)) {
                        $pidpr_d=$row1['idpr_d'];
                        $ptgl=$row1['tanggal'];
                        $pnmtipe=$row1['nama_tipe'];
                        $pkryid=$row1['karyawanid'];
                        $pkrynm=$row1['nama_karyawan'];
                        $pnmbarang=$row1['namabarang'];
                        $pspesifikasi=$row1['spesifikasi1'];
                        $pketerangan=$row1['keterangan'];
                        $pnotes=$row1['aktivitas'];
                        $npengajuan=$row1['pengajuan'];
                        $puserinput=$row1['nama_user'];
                        $psudahisivendor=$row1['sudahisivendor'];
                        $pusrinputdetail=$row1['useridinputdetail'];
                        $pusrnamadetail=$row1['nmuserinput'];
                        
                        $ptglatasan1=$row1['tgl_atasan1'];
                        $ptglatasan2=$row1['tgl_atasan2'];
                        $ptglatasan3=$row1['tgl_atasan3'];
                        $ptglatasan4=$row1['tgl_atasan4'];
                        $ptglatasan5=$row1['tgl_atasan5'];
                        $ptglval1=$row1['tgl_validate1'];
                        $ptglval2=$row1['tgl_validate2'];

                        $pidatasan1=$row1['atasan1'];
                        $pidatasan2=$row1['atasan2'];
                        $pidatasan3=$row1['atasan3'];
                        $pidatasan4=$row1['atasan4'];
                        $pidatasan5=$row1['atasan5'];
                        $puserval1=$row1['validate1'];
                        $puserval2=$row1['validate2'];
                    
                        if ($ptglatasan1=="0000-00-00" OR $ptglatasan1=="0000-00-00 00:00:00") $ptglatasan1="";
                        if ($ptglatasan2=="0000-00-00" OR $ptglatasan2=="0000-00-00 00:00:00") $ptglatasan2="";
                        if ($ptglatasan3=="0000-00-00" OR $ptglatasan3=="0000-00-00 00:00:00") $ptglatasan3="";
                        if ($ptglatasan4=="0000-00-00" OR $ptglatasan4=="0000-00-00 00:00:00") $ptglatasan4="";
                        if ($ptglatasan5=="0000-00-00" OR $ptglatasan5=="0000-00-00 00:00:00") $ptglatasan5="";
                        if ($ptglval1=="0000-00-00" OR $ptglval1=="0000-00-00 00:00:00") $ptglval1="";
                        if ($ptglval2=="0000-00-00" OR $ptglval2=="0000-00-00 00:00:00") $ptglval2="";
                        
                        
                        $pjml=$row1['jml'];
                        $pharga=$row1['rp_pr'];
                        
                        $ptgl= date("d/m/Y", strtotime($ptgl));
                        $pjml=number_format($pjml,0,",",",");
                        $pharga=number_format($pharga,0,",",",");
                        
                        
                        $pwarnafld1="btn btn-default btn-xs";
                        $pwarnafld2="btn btn-default btn-xs";
                        
                        $phapus="<input type='button' value='Hapus' class='btn btn-danger btn-xs' onClick=\"ProsesDataHapusDetail('hapus', '$pidpr', '$pidpr_d')\">";
                        $pedit="<a class='btn btn-warning btn-xs' href='?module=$pmodule&act=editdata&idmenu=$pidmenu&nmun=$pidmenu&xd=$pidpr_d&id=$pidpr'>Edit</a>";
                        
                        $pketgsmhos="GSM";
                    
                        $pstsapvoleh="";
                        
                        if ($npengajuan=="HO") {
                            $pketgsmhos="Atasan";
                            if (empty($ptglatasan4) AND !empty($pidatasan4)) { $pstsapvoleh="Belum Approve $pketgsmhos"; $ceklisnya=""; $pedit=""; $phapus=""; }
                        }elseif ($npengajuan=="OTC" OR $npengajuan=="CHC") {
                            $pketgsmhos="HOS";
                            if (empty($ptglatasan4) AND !empty($pidatasan4)) { $pstsapvoleh="Belum Approve $pketgsmhos"; $ceklisnya=""; $pedit=""; $phapus=""; }
                        }else{
                            if (empty($ptglatasan4) AND !empty($pidatasan4)) { $pstsapvoleh="Belum Approve $pketgsmhos"; $ceklisnya=""; $pedit=""; $phapus=""; }
                            if (empty($ptglatasan3) AND !empty($pidatasan3)) { $pstsapvoleh="Belum Approve SM"; $ceklisnya=""; $pedit=""; $phapus=""; }
                            if (empty($ptglatasan2) AND !empty($pidatasan2)) { $pstsapvoleh="Belum Approve DM"; $ceklisnya=""; $pedit=""; $phapus=""; }
                            if (empty($ptglatasan1) AND !empty($pidatasan1)) { $pstsapvoleh="Belum Approve SPV/AM"; $ceklisnya=""; $pedit=""; $phapus=""; }
                        }
                        if (!empty($pstsapvoleh)) {
                            $pstsapvoleh="<span style='color:red;'>$pstsapvoleh</span>";
                        }
                        
                        
                        if (!empty($puserval2)) {
                            $pstsapvoleh="<span style='color:blue;'>Sudah Proses Purchasing</span>";
                            $ceklisnya="";
                        }
                        
                        if ($ppilihsts=="UNAPPROVE") {
                            $pedit=""; $phapus="";
                        }
                        
                        if ($psudahisivendor=="Y") {
                            $ceklisnya="";
                            $pedit=""; $phapus="";
                        }
                        
                        
                        if ((INT)$purutannomor==1 AND empty($ceklisnya)) {
                            $padaapprove="";
                        }
                        
                        $philangkandata="";
                        if ($ppilihsts=="APPROVE" AND empty($padaapprove)) {
                            $philangkandata=" class='divnone' ";
                        }
                        
                        if ($pusrinputdetail==$pidcard){
                        }else{
                            $phapus="";
                        }
                        
                        echo "<tr $philangkandata>";
                        
                        echo "<td nowrap>$pnomornya</td>";
                        echo "<td nowrap class='divnone'>$pidpr $pnmtipe $pkrynm $puserinput $ptgl </td>";
                        echo "<td nowrap>$ceklisnya</td>";
                        echo "<td nowrap>$pprint</td>";
                        echo "<td nowrap>$pedit $phapus</td>";
                        echo "<td nowrap>$pidpr_d</td>";
                        echo "<td nowrap>$ptgl</td>";
                        echo "<td nowrap>$pkrynm</td>";
                        
                        echo "<td nowrap>$pnmbarang</td>";
                        echo "<td >$pspesifikasi</td>";
                        echo "<td >$pketerangan</td>";
                        echo "<td nowrap align='right'>$pjml</td>";
                        echo "<td nowrap align='right'>$pharga</td>";
                        echo "<td nowrap>$pnmtipe</td>";
                        echo "<td nowrap>$pstsapvoleh</td>";
                        echo "<td nowrap>$pusrnamadetail</td>";
                        
                        echo "</tr>";
                        
                        $pbelumlewat=true;
                        
                        $ceklisnya="";
                        $pprint="";
                        $pnomornya="";
                        $purutannomor++;
                    }
                    
                    $no++;
                }
                ?>
            </tbody>
        </table>
        
    </div>

    <?PHP
    if ($ppilihsts=="UNAPPROVE") {
    ?>
        <div class='clearfix'></div>
        <div class="well" style="margin-top: -5px; margin-bottom: 5px; padding-top: 10px; padding-bottom: 6px;"><!--overflow: auto; -->
            <input class='btn btn-success' type='button' name='buttonapv' value='UnProses' 
               onClick="ProsesDataUnProses('unproses', 'chkbox_br[]')">
        </div>
    <?PHP
    }
    ?>
    
    <!-- tanda tangan -->
    <?PHP
        if ($ppilihsts=="APPROVE") {
            echo "<div class='col-sm-5'>";
            include "ttd_prosit.php";
            echo "</div>";
        }
    ?>
    
</form>


<script>       
    function SelAllCheckBox(nmbuton, data){
        var checkboxes = document.getElementsByName(data);
        var button = document.getElementById(nmbuton);
        if(button.value == 'select'){
            for (var i in checkboxes){
                checkboxes[i].checked = 'FALSE';
            }
            button.value = 'deselect'
        }else{
            for (var i in checkboxes){
                checkboxes[i].checked = '';
            }
            button.value = 'select';
        }
    }
    
    function ProsesDataHapusDetail(ket, cidpr, cidprd) {
        ok_ = 1;
        if (ok_) {
            var r = confirm('Apakah akan melakukan proses '+ket+' ...?');
            if (r==true) {

                var myurl = window.location;
                var urlku = new URL(myurl);
                var module = urlku.searchParams.get("module");
                var idmenu = urlku.searchParams.get("idmenu");

                //document.write("You pressed OK!")
                document.getElementById("d-form2").action = "module/purchasing/pch_prosesprit/aksi_prosesprit.php?module="+module+"&idmenu="+idmenu+"&act="+ket+"&kethapus="+"&ket="+ket+"&id="+cidpr+"&idd="+cidprd;
                document.getElementById("d-form2").submit();
                return 1;
            }
        } else {
            //document.write("You pressed Cancel!")
            return 0;
        }
    }
    
    
    function ProsesDataUnProses(ket, cekbr){
        //alert(ket+", "+cekbr);
        var cmt = confirm('Apakah akan melakukan unproses...?');
        if (cmt == false) {
            return false;
        }
        var allnobr = "";
        
        var chk_arr =  document.getElementsByName(cekbr);
        var chklength = chk_arr.length;
        var allnobr="";
        for(k=0;k< chklength;k++)
        {
            if (chk_arr[k].checked == true) {
                allnobr =allnobr + "'"+chk_arr[k].value+"',";
            }
        }
        if (allnobr.length > 0) {
            var lastIndex = allnobr.lastIndexOf(",");
            allnobr = "("+allnobr.substring(0, lastIndex)+")";
        }else{
            alert("Tidak ada data yang diproses...!!!");
            return false;
        }
        
        
        
        var txt;
        var ekaryawan=document.getElementById('cb_karyawan').value;
            
        var myurl = window.location;
        var urlku = new URL(myurl);
        var module = urlku.searchParams.get("module");
        var idmenu = urlku.searchParams.get("idmenu");
        
        $.ajax({
            type:"post",
            url:"module/purchasing/pch_prosesprit/simpan_prosesit.php?module="+module+"&idmenu="+idmenu+"&act="+ket,
            data:"ket=unapprove"+"&unobr="+allnobr+"&ukaryawan="+ekaryawan+"&ketrejpen="+txt,
            success:function(data){
                pilihData('unapprove');
                alert(data);
            }
        });
        
        
    }
    
</script>

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


<?PHP
hapusdata:
    mysqli_query($cnmy, "drop TEMPORARY table if EXISTS $tmp01");
    mysqli_query($cnmy, "drop TEMPORARY table if EXISTS $tmp02");
    mysqli_query($cnmy, "drop TEMPORARY table if EXISTS $tmp03");
    mysqli_close($cnmy);
?>