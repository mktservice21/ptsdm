<?PHP
    include "config/cek_akses_modul.php";
    $hari_ini = date("Y-m-d");
    $hari_ini2 = date("Y-01-d");
    //$tgl_pertama = date('F Y', strtotime('-2 month', strtotime($hari_ini)));
    $tgl_pertama = date('F Y', strtotime($hari_ini2));
    $tgl_akhir = date('F Y', strtotime($hari_ini));
    
    if (!empty($_SESSION['SPDPERENTYOTC1'])) $tgl_pertama = $_SESSION['SPDPERENTYOTC1'];
    if (!empty($_SESSION['SPDPERENTYOTC2'])) $tgl_akhir = $_SESSION['SPDPERENTYOTC2'];
    
    $fkaryawan=$_SESSION['IDCARD'];
    $fstsadmin=$_SESSION['STSADMIN'];
    $flvlposisi=$_SESSION['LVLPOSISI'];
    $fdivisi=$_SESSION['DIVISI'];
	
    if (isset($_GET['act'])) {
        if ($_GET['act']!="editdata" AND $_GET['act']!="tambahbaru") {
            mysqli_query($cnmy, "CALL dbmaster.proses_outstanding_br_otc_pertahun()");
        }
    }
?>

<div class="">

    <div class="page-title"><div class="title_left">
            <h3>
                <?PHP
                $judul="Permintaan Dana OTC";
                if ($_GET['act']=="tambahbaru")
                    echo "Input $judul";
                elseif ($_GET['act']=="editdata")
                    echo "Edit $judul";
                else
                    echo "Data $judul";
                ?>
            </h3>
    </div></div><div class="clearfix"></div>
    <!--row-->
    <div class="row">

        <?php
        //$aksi="module/mod_br_spd/laporanbrbulan.php";
        $aksi="eksekusi3.php";
        switch($_GET['act']){
            default:
                ?>
        
                <script type="text/javascript" language="javascript" >
                    
                    function RefreshDataTabel() {
                        KlikDataTabel();
                    }

                    $(document).ready(function() {
                        KlikDataTabel();
                    } );

                    function KlikDataTabel() {
                        var eaksi = "module/mod_br_spdotc/aksi_spdotc.php";
                        var ekryid=document.getElementById('cb_karyawan').value;
                        var etgl1=document.getElementById('tgl1').value;
                        var etgl2=document.getElementById('tgl2').value;

                        $("#loading").html("<center><img src='images/loading.gif' width='50px'/></center>");
                        $.ajax({
                            type:"post",
                            url:"module/mod_br_spdotc/viewdatatabel.php?module=viewdata",
                            data:"uperiode1="+etgl1+"&uperiode2="+etgl2+"&uaksi="+eaksi+"&ukryid="+ekryid,
                            success:function(data){
                                $("#c-data").html(data);
                                $("#loading").html("");
                            }
                        });
                    }

                </script>

                    
                <div class='col-md-12 col-sm-12 col-xs-12'>
                    <div class='x_panel'>

                        <div class='x_title'>
                            <h2><input class='btn btn-default' type=button value='Tambah Baru'
                                onclick="window.location.href='<?PHP echo "?module=$_GET[module]&idmenu=$_GET[idmenu]&act=tambahbaru"; ?>';">
                                <small></small>
                            </h2>
                            <div class='clearfix'></div>
                        </div>

                        <form method='POST' action='<?PHP echo "$aksi?module=$_GET[module]&act=input&idmenu=$_GET[idmenu]"; ?>' 
                              id='demo-form2' name='form1' data-parsley-validate class='form-horizontal form-label-left' target="_blank">

                            <div class='col-sm-3'>
                                Yang membuat
                                <div class="form-group">
                                    <select class='form-control' id="cb_karyawan" name="cb_karyawan" onchange="">
                                        <?PHP
                                            //if ($pidgroup=="1" OR $pidgroup=="24") {
                                                $query = "select karyawanId as karyawanid, nama as nama_karyawan from hrd.karyawan WHERE 1=1 ";
                                                $query .=" AND jabatanid NOT IN ('15', '10', '18', '08', '20', '05')";
                                            //}else{
                                            //    $query = "select karyawanId as karyawanid, nama as nama_karyawan from hrd.karyawan WHERE karyawanId='$fkaryawan' ";
                                            //}
                                            $query .= " Order by nama, karyawanId";
                                            $tampilket= mysqli_query($cnmy, $query);
                                            $ketemu=mysqli_num_rows($tampilket);
                                            //if ((INT)$ketemu<=0) 
                                            echo "<option value='' selected>-- Pilih --</option>";

                                            while ($du= mysqli_fetch_array($tampilket)) {
                                                $nidkry=$du['karyawanid'];
                                                $nnmkry=$du['nama_karyawan'];
                                                $nidkry_=(INT)$nidkry;
                                                echo "<option value='$nidkry'>$nnmkry ($nidkry_)</option>";
                                                /*
                                                if ($nidkry==$fkaryawan)
                                                    echo "<option value='$nidkry' selected>$nnmkry ($nidkry_)</option>";
                                                else
                                                    echo "<option value='$nidkry'>$nnmkry ($nidkry_)</option>";
                                                */
                                            }

                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class='col-sm-2'>
                                Periode Permintaan
                                <div class="form-group">
                                    <div class='input-group date' id='cbln01'>
                                        <input type='text' id='tgl1' name='e_periode01' required='required' class='form-control input-sm' placeholder='tgl awal' value='<?PHP echo $tgl_pertama; ?>' placeholder='dd mmm yyyy' Readonly>
                                        <span class="input-group-addon">
                                           <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class='col-sm-2'>
                               <small>s/d.</small>
                               <div class="form-group">
                                   <div class='input-group date' id='cbln02'>
                                       <input type='text' id='tgl2' name='e_periode02' required='required' class='form-control input-sm' placeholder='tgl akhir' value='<?PHP echo $tgl_akhir; ?>' placeholder='dd mmm yyyy' Readonly>
                                       <span class="input-group-addon">
                                          <span class="glyphicon glyphicon-calendar"></span>
                                       </span>
                                   </div>
                               </div>
                           </div>

                            <div class='col-sm-3'>
                                <small>&nbsp;</small>
                               <div class="form-group">
                                   <input type='button' class='btn btn-success btn-xs' id="s-submit" value="View Data" onclick="RefreshDataTabel()">&nbsp;
                               </div>
                           </div>
                       </form>

                        <div id='loading'></div>
                        <div id='c-data'>

                        </div>

                    </div>
                </div>
                
                
                

                <?PHP

            break;

            case "tambahbaru":
                include "tambah.php";
            break;

            case "editdata":
                include "tambah.php";
            break;
        
        }
        ?>

    </div>
    <!--end row-->
</div>

