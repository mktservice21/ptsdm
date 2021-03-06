<?PHP include "config/cek_akses_modul.php"; ?>

<div class="">

    <div class="col-md-12 col-sm-12 col-xs-12"><div class="title_left"><h3>Rekap Incentive MR AM dan DM</h3></div></div><div class="clearfix"></div>
    <!--row-->
    <div class="row">
        <?php
        include "config/koneksimysqli_ms.php";
        $pidkaryawan=$_SESSION['IDCARD'];
        $pidjabatan=$_SESSION['JABATANID'];
        $pidgroup=$_SESSION['GROUP'];
        
        $aksi="eksekusi3.php";
        switch($_GET['act']){
            default:

                $hari_ini = date("Y-m-d");
                //$tgl_pertama=date('F Y', strtotime('-1 month', strtotime($hari_ini)));
                $tgl_pertama = date('F Y', strtotime($hari_ini));
                $pbulan= date('Y-m', strtotime($hari_ini));
                ?>
                <script>
                    function disp_confirm(pText)  {
                        if (pText == "excel") {
                            document.getElementById("form1").action = "<?PHP echo "$aksi?module=$_GET[module]&act=input&idmenu=$_GET[idmenu]&ket=excel"; ?>";
                            document.getElementById("form1").submit();
                            return 1;
                        }else{
                            document.getElementById("form1").action = "<?PHP echo "$aksi?module=$_GET[module]&act=input&idmenu=$_GET[idmenu]&ket=bukan"; ?>";
                            document.getElementById("form1").submit();
                            return 1;
                        }
                    }

                    function cekTipeRpt() {
                        var itipe = document.getElementById('cb_rpttipe').value;
                        if (itipe=="S") {
                            ireport.style.display = "none";
                        }else{
                            ireport.style.display = "block";
                        }
                        
                    }
                </script>

                <script type="text/javascript">
                    $(document).ready(function() {
                        $('#cbln01').on('change dp.change', function(e){
                            
                        });
                    });

                    
                    $(document).ready(function() {
                        cekTipeRpt();
                    } );
                </script>

                <div class='col-md-12 col-sm-12 col-xs-12'>
                    <div class='x_panel'>
                        <form name='form1' id='form1' method='POST' action="<?PHP echo "$aksi?module=$_GET[module]&act=input&idmenu=$_GET[idmenu]"; ?>" enctype='multipart/form-data' target="_blank">
                            <div class='col-md-12 col-sm-12 col-xs-12'>
                                <h2>
                                    <button type='button' class='btn btn-success' onclick="disp_confirm('')">Preview</button>
                                    <button type='button' class='btn btn-danger' onclick="disp_confirm('excel')">Excel</button>
                                    <a class='btn btn-default' href="<?PHP echo "?module=home"; ?>">Home</a>
                                </h2>
                                <div class='clearfix'></div>
                            </div>

                            <!--kiri-->
                            <div class='col-md-6 col-xs-12'>
                                <div class='x_panel'>
                                    <div class='x_content form-horizontal form-label-left'><br />
                                        
                                        
                                        <div class='form-group'>
                                            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='cbln01'>Bulan <span class='required'></span></label>
                                            <div class='col-md-8'>
                                                <div class="form-group">
                                                    <div class='input-group date' id=''>
                                                        <input type='text' id='cbln01' name='bulan' required='required' class='form-control' placeholder='tgl awal' value='<?PHP echo $tgl_pertama; ?>' placeholder='dd mmm yyyy' Readonly>
                                                        <span class="input-group-addon">
                                                           <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class='form-group'>
                                        <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Jabatan <span class='required'></span></label>
                                            <div class='col-md-9 col-sm-9 col-xs-12'>
                                                <select class='form-control' name='cb_jabatan' id='cb_jabatan' onchange="">
                                                    <?PHP
                                                    echo "<option value='' selected>All</option>";
                                                    echo "<option value='MR'>MR</option>";
                                                    echo "<option value='AM'>AM</option>";
                                                    echo "<option value='DM'>DM</option>";
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class='form-group'>
                                        <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Incentive From <span class='required'></span></label>
                                            <div class='col-md-9 col-sm-9 col-xs-12'>
                                                <select class='form-control' name='cb_from' id='cb_from' onchange="">
                                                    <?PHP
                                                    echo "<option value='GSM' selected>GSM</option>";
                                                    echo "<option value='PM'>PM</option>";
                                                    echo "<option value='ALL'>--ALL--</option>";
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class='form-group'>
                                        <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Type <span class='required'></span></label>
                                            <div class='col-md-9 col-sm-9 col-xs-12'>
                                                <select class='form-control' name='cb_rpttipe' id='cb_rpttipe' onchange="cekTipeRpt()">
                                                    <?PHP
                                                    echo "<option value='S' selected>Summary</option>";
                                                    echo "<option value='D'>Detail</option>";
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div id='ireport' class='form-group'>
                                        <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Report Type <span class='required'></span></label>
                                            <div class='col-md-9 col-sm-9 col-xs-12'>
                                                <select class='form-control' name='cb_report' id='cb_report' onchange="">
                                                    <?PHP
                                                    echo "<option value='N'>Normal</option>";
                                                    echo "<option value='P' selected>Pivot</option>";
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        
                                    </div>
                                </div>
                            </div>
                            <!--end kiri-->

                        </form>
                    </div><!--end xpanel-->
                </div>
                
                


                
                <?PHP
            break;

            case "tambahbaru":

            break;
        }
        ?>
    </div>
    <!--end row-->
</div>