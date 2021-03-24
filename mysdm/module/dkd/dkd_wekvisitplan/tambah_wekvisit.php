<?PHP

$pidmodule=$_GET['module'];
$pidmenu=$_GET['idmenu'];
$pidact=$_GET['act'];

$piduser=$_SESSION['USERID']; 
$pidcard=$_SESSION['IDCARD'];
$pidjbt=$_SESSION['JABATANID']; 
$pidgroup=$_SESSION['GROUP']; 
$pnamalengkap=$_SESSION['NAMALENGKAP'];

$pcabangid="";

    $query ="SELECT a.karyawanid, b.nama nama_karyawan, a.spv, c.nama nama_spv, 
        a.dm, d.nama nama_dm, a.sm, e.nama nama_sm, a.gsm, f.nama nama_gsm, 
        a.icabangid as icabangid, a.areaid as areaid, a.jabatanid as jabatanid 
        FROM dbmaster.t_karyawan_posisi a 
        LEFT JOIN hrd.karyawan b on a.karyawanId=b.karyawanId 
        LEFT JOIN hrd.karyawan c on a.spv=c.karyawanId 
        LEFT JOIN hrd.karyawan d on a.dm=d.karyawanId 
        LEFT JOIN hrd.karyawan e on a.sm=e.karyawanId 
        LEFT JOIN hrd.karyawan f on a.gsm=f.karyawanId WHERE a.karyawanid='$pidcard'";
    $ptampil= mysqli_query($cnmy, $query);
    $nrs= mysqli_fetch_array($ptampil);
    $pkdspv=$nrs['spv'];
    $pnamaspv=$nrs['nama_spv'];
    $pkddm=$nrs['dm'];
    $pnamadm=$nrs['nama_dm'];
    $pkdsm=$nrs['sm'];
    $pnamasm=$nrs['nama_sm'];
    $pkdgsm=$nrs['gsm'];
    $pnamagsm=$nrs['nama_gsm'];


    $pcabangid=$nrs['icabangid'];
    $pareaid=$nrs['areaid'];
    $pjabatanid=$nrs['jabatanid'];


    $query = "select icabangid as icabangid, areaid as areaid, jabatanid as jabatanid from hrd.karyawan where karyawanid='$pidcard'";
    $tampil= mysqli_query($cnmy, $query);
    $rowx= mysqli_fetch_array($tampil);
    if (empty($pcabangid)) $pcabangid=$rowx['icabangid'];
    if (empty($pareaid)) $pareaid=$rowx['areaid'];
    if (empty($pjabatanid)) $pjabatanid=$rowx['jabatanid'];

    $picabidfil="";
    if ($pidjbt=="38" || (DOUBLE)$pidjbt==38) {
        $pcabangid="";
        $query = "select distinct karyawanid as karyawanid, icabangid as icabangid from hrd.rsm_auth where karyawanid='$pidcard'";
        $tampil= mysqli_query($cnmy, $query);
        while ($nro= mysqli_fetch_array($tampil)) {
            $pncab=$nro['icabangid'];
            if ($pncab=="0000000003" OR $pncab=="0000000114") {
                $pcabangid=$pncab;
            }else{
                if (empty($pcabangid)) $pcabangid=$pncab;
            }


            $picabidfil .="'".$pncab."',";
        }
        if (!empty($picabidfil)) {
            $picabidfil="(".substr($picabidfil, 0, -1).")";
        }else{
            $picabidfil="('nnzznnnn')";
        }

    }elseif ($pidjbt=="08" || (DOUBLE)$pidjbt==8) {
        $pcabangid="";
        $query = "select distinct karyawanid as karyawanid, icabangid as icabangid from MKT.idm0 where karyawanid='$pidcard'";
        $tampil= mysqli_query($cnmy, $query);
        while ($nro= mysqli_fetch_array($tampil)) {
            $pncab=$nro['icabangid'];
            if ($pncab=="0000000003" OR $pncab=="0000000114") {
                $pcabangid=$pncab;
            }else{
                if (empty($pcabangid)) $pcabangid=$pncab;
            }


            $picabidfil .="'".$pncab."',";
        }
        if (!empty($picabidfil)) {
            $picabidfil="(".substr($picabidfil, 0, -1).")";
        }else{
            $picabidfil="('nnzznnnn')";
        }

    }

    $pidcabang=$pcabangid;


    $query= "select DISTINCT a.karyawanid as karyawanid, b.nama as nama from MKT.idm0 a JOIN hrd.karyawan b on a.karyawanid=b.karyawanid "
            . " WHERE a.icabangid='$pcabangid' AND IFNULL(a.karyawanid,'')<>'' "
            . " AND (IFNULL(b.tglkeluar,'0000-00-00')='0000-00-00' OR IFNULL(b.tglkeluar,'')='')";
    $tampil= mysqli_query($cnmy, $query);
    $rowd= mysqli_fetch_array($tampil);
    $pnnkrydm=$rowd['karyawanid'];
    $pnnmkrydm=$rowd['nama'];
    if (!empty($pnnkrydm)) {
        $pkdspv=""; $pnamaspv="";
        $pkddm=$pnnkrydm;
        $pnamadm=$pnnmkrydm;
    }
    
    $query= "select DISTINCT a.karyawanid as karyawanid, b.nama as nama from MKT.ism0 a JOIN hrd.karyawan b on a.karyawanid=b.karyawanid "
            . " WHERE a.icabangid='$pcabangid' AND IFNULL(a.karyawanid,'')<>'' "
            . " AND (IFNULL(b.tglkeluar,'0000-00-00')='0000-00-00' OR IFNULL(b.tglkeluar,'')='')";
    $tampil= mysqli_query($cnmy, $query);
    $rowd2= mysqli_fetch_array($tampil);
    $pnnkrydm=$rowd2['karyawanid'];
    $pnnmkrydm=$rowd2['nama'];
    if (!empty($pnnkrydm)) {
        $pkdsm=$pnnkrydm;
        $pnamasm=$pnnmkrydm;
        $pkdgsm="";
        $pnamagsm="";
    }
    
    
    
    $query = "select a.gsm, b.nama as nama_gsm FROM dbmaster.t_karyawan_posisi a JOIN hrd.karyawan b on a.gsm=b.karyawanid WHERE a.karyawanid='$pkdsm'";
    $ptampil2= mysqli_query($cnmy, $query);
    $nrs2= mysqli_fetch_array($ptampil2);

    $pkdgsm=$nrs2['gsm'];
    $pnamagsm=$nrs2['nama_gsm'];

    if ($pcabangid=="0000000003") {
        $pkdspv="";
        $pnamaspv="";
        $pkddm="";
        $pnamadm="";
    }

    if ($pcabangid=="00000000114") {
        $pkdspv="";
        $pnamaspv="";
        $pkddm="";
        $pnamadm="";
        $pkdsm="";
        $pnamasm="";
    }

    if ($pidjbt=="08" || (DOUBLE)$pidjbt==8) {
        $pkdspv="";
        $pnamaspv="";
        $pkddm="";
        $pnamadm="";
    }


// END CABANG & ATASAN



$pidinput="";


$hari_ini = date('Y-m-d');
$pdate = date('Y-m-d', strtotime('+1 days', strtotime($hari_ini)));
$pnewdate = strtotime ( 'monday 0 week' , strtotime ( $pdate ) ) ;
$tgl_pertama = date ( 'd F Y' , $pnewdate );

$ppketstatus="000";//blank
$paktivitas="";
$pcompl="";
$pjmlrec=1;

$act="input";
if ($pidact=="editdata"){
    $act="update";
    $pidinput=$_GET['id'];

}


?>
<div class="">

    
    <!--row-->
    <div class="row">


        <div class='col-md-12 col-sm-12 col-xs-12'>
            
            <div class='x_panel'>

                <form method='POST' action='<?PHP echo "$aksi?module=$pidmodule&act=input&idmenu=$pidmenu"; ?>' 
                        id='form_data1' name='form1' data-parsley-validate class='form-horizontal form-label-left'  enctype='multipart/form-data'>
                        
                    
                    <div class='col-md-12 col-sm-12 col-xs-12'>
                        <h2>
                            <a class='btn btn-default' href="<?PHP echo "?module=$pidmodule&idmenu=$pidmenu&act=$pidmenu"; ?>">Back</a>
                        </h2>
                        <div class='clearfix'></div>
                    </div>


                    <div class='x_panel'>
                        <div class='x_content'>
                            
                            
                            <div class='col-md-12 col-sm-12 col-xs-12'>

                                <div hidden class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>ID <span class='required'></span></label>
                                    <div class='col-md-4'>
                                        <input type='text' id='e_id' name='e_id' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pidinput; ?>' Readonly>
                                        <input type='text' id='e_idinputuser' name='e_idinputuser' class='form-control col-md-7 col-xs-12' value='<?PHP echo $piduser; ?>' Readonly>
                                        <input type='text' id='e_idcarduser' name='e_idcarduser' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pidcard; ?>' Readonly>
                                    </div>
                                </div>

                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Nama <span class='required'></span></label>
                                    <div class='col-md-4'>
                                        <input type='text' id='e_namauser' name='e_namauser' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pnamalengkap; ?>' Readonly>
                                    </div>
                                </div>

                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Tanggal <span class='required'></span></label>
                                    <div class='col-md-4'>
                                        <div class='input-group date' id=''>
                                            <input type="text" class="form-control" id='e_periode1' name='e_periode1' autocomplete='off' required='required' placeholder='d F Y' value='<?PHP echo $tgl_pertama; ?>' Readonly>
                                            <span class='input-group-addon'>
                                                <span class='glyphicon glyphicon-calendar'></span>
                                            </span>

                                        </div>
                                    </div>
                                </div>


                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Keperluan <span class='required'></span></label>
                                    <div class='col-xs-4'>
                                        <select class='soflow' name='cb_ketid' id='cb_ketid' onchange="">
                                            <?php
                                            $query = "select ketId as ketid, nama as nama From hrd.ket order by ketId, nama";
                                            $tampilket= mysqli_query($cnmy, $query);
                                            while ($du= mysqli_fetch_array($tampilket)) {
                                                $nidket=$du['ketid'];
                                                $nnmket=$du['nama'];

                                                if ($nidket==$ppketstatus) 
                                                    echo "<option value='$nidket' selected>$nnmket</option>";
                                                else
                                                    echo "<option value='$nidket'>$nnmket</option>";

                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Compl <span class='required'></span></label>
                                    <div class='col-xs-4'>
                                        <input type='text' id='e_compl' name='e_compl' class='form-control col-md-7 col-xs-12' maxlength="150" value='<?PHP echo $pcompl; ?>'>
                                    </div>
                                </div>


                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Aktivitas <span class='required'></span></label>
                                    <div class='col-md-4'>
                                    <textarea class='form-control' id="e_aktivitas" name='e_aktivitas' maxlength="250"><?PHP echo $paktivitas; ?></textarea>
                                    </div>
                                </div>





                            </div>


                        </div>
                    </div>


                        
                            
                    <!--kiri-->
                    <div class='col-md-6 col-xs-12'>
                        <div class='x_panel'>
                            <div class='x_content form-horizontal form-label-left'>

                                <div hidden class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>ID JML <span class='required'></span></label>
                                    <div class='col-xs-6'>
                                        <input type='text' id='e_idjmlrec' name='e_idjmlrec' class='form-control col-md-7 col-xs-12 inputmaskrp2' value='<?PHP echo $pjmlrec; ?>' Readonly>
                                    </div>
                                </div>

                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>JV <span class='required'></span></label>
                                    <div class='col-xs-4'>
                                        <select class='soflow' name='cb_jv' id='cb_jv' onchange="">
                                            <?php
                                            echo "<option value='N' selected>N</option>";
                                            echo "<option value='Y'>Y</option>";
                                            ?>
                                        </select>
                                    </div>
                                </div>


                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Dokter <span class='required'></span></label>
                                    <div class='col-xs-4'>
                                        <select class='soflow' name='cb_doktid' id='cb_doktid' onchange="">
                                            <?php
                                            $ipcabid="0000000094";
                                            $query = "select `id` as iddokter, namalengkap, gelar, spesialis from dr.masterdokter WHERE 1=1 ";
                                            $query .=" AND icabangid='$ipcabid' ";
                                            $query .=" order by namalengkap, `id`";
                                            $query .=" limit 100";
                                            $tampilket= mysqli_query($cnmy, $query);
                                            while ($du= mysqli_fetch_array($tampilket)) {
                                                $niddokt=$du['iddokter'];
                                                $nnmdokt=$du['namalengkap'];
                                                $ngelar=$du['gelar'];
                                                $nspesial=$du['spesialis'];

                                                echo "<option value='$niddokt'>$nnmdokt ($ngelar), $nspesial</option>";

                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>


                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>Notes <span class='required'></span></label>
                                    <div class='col-xs-9'>
                                        <textarea class='form-control' id="e_ketdetail" name='e_ketdetail' maxlength='300'></textarea>
                                    </div>
                                </div>

                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>&nbsp; <span class='required'></span></label>
                                    <div class='col-xs-9'>
                                        <button type='button' class='btn btn-dark btn-xs add-row' onclick='TambahDataBarang("")'>&nbsp; &nbsp; &nbsp; Tambah &nbsp; &nbsp; &nbsp;</button>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>

                    <!--kanan-->
                    <div class='col-md-6 col-xs-12'>
                        <div class='x_panel'>
                            <div class='x_content form-horizontal form-label-left'>
                                
                                <div id='loading3'></div>
                                <div id="s_div">

                                    <div class='x_content'>

                                        <table id='datatablestockopn' class='table table-striped table-bordered' width='100%'>
                                            <thead>
                                                <tr>
                                                    <th width='5px' nowrap></th>
                                                    <th width='10px' align='center' class='divnone'></th><!--class='divnone' -->
                                                    <th width='5px' align='center'>&nbsp;</th>
                                                    <th width='5px' align='center'>JV</th>
                                                    <th width='200px' align='center'>Nama Dokter</th>
                                                    <th width='200px' align='center'>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody class='inputdata'>
                                            <?PHP
                                                $nnjmlrc=0;
                                                echo "<input type='hidden' id='m_idjmrec[$nnjmlrc]' name='m_idjmrec[]' value='$nnjmlrc' Readonly>";
                                                echo "<input type='hidden' id='m_iddokt[$nnjmlrc]' name='m_iddokt[$nnjmlrc]' value=''>";
                                            ?>
                                            </tbody>
                                        </table>
                                        <button type='button' class='btn btn-danger btn-xs delete-row' >&nbsp; &nbsp; Hapus &nbsp; &nbsp;</button>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                    <div class='x_panel'>
                        <div class='x_content'>
                            
                            
                            <div class='col-md-12 col-sm-12 col-xs-12'>


                                <div class='form-group'>
                                    <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>&nbsp; <span class='required'></span></label>
                                    <div class='col-md-4'>
                                        <button type='button' class='btn btn-success' onclick='disp_confirm("Simpan ?", "<?PHP echo $act; ?>")'>Save</button>
                                    </div>
                                </div>

                                

                                <br/>
                                <div hidden id="div_atasan">
                                    
                                    <div class='form-group'>
                                        <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>SPV / AM <span class='required'></span></label>
                                        <div class='col-xs-3'>
                                            <input type='hidden' id='e_kdspv' name='e_kdspv' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pkdspv; ?>'>
                                            <input type='text' id='e_namaspv' name='e_namaspv' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pnamaspv; ?>'>
                                        </div>
                                    </div>
                                    
                                    <div class='form-group'>
                                        <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>DM <span class='required'></span></label>
                                        <div class='col-xs-3'>
                                            <input type='hidden' id='e_kddm' name='e_kddm' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pkddm; ?>'>
                                            <input type='text' id='e_namadm' name='e_namadm' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pnamadm; ?>'>
                                        </div>
                                    </div>
                                    
                                    <div class='form-group'>
                                        <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>SM <span class='required'></span></label>
                                        <div class='col-xs-3'>
                                            <input type='hidden' id='e_kdsm' name='e_kdsm' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pkdsm; ?>'>
                                            <input type='text' id='e_namasm' name='e_namasm' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pnamasm; ?>'>
                                        </div>
                                    </div>
                                    
                                    <div class='form-group'>
                                        <label class='control-label col-md-3 col-sm-3 col-xs-12' for=''>GSM <span class='required'></span></label>
                                        <div class='col-xs-3'>
                                            <input type='hidden' id='e_kdgsm' name='e_kdgsm' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pkdgsm; ?>'>
                                            <input type='text' id='e_namagsm' name='e_namagsm' class='form-control col-md-7 col-xs-12' value='<?PHP echo $pnamagsm; ?>'>
                                        </div>
                                    </div>
                                    
                                </div>

                            </div>

                        </div>
                    </div>


                </form>

            </div>

        </div>


    </div>

</div>




<script>
    $(document).ready(function(){
        

        var element = document.getElementById("div_atasan");
        //element.classList.remove("disabledDiv");
        element.classList.add("disabledDiv");

        $("#add_new").click(function(){
            $(".entry-form").fadeIn("fast");
        });

        $("#close").click(function(){
            $(".entry-form").fadeOut("fast");
        });

        $("#cancel").click(function(){
            $(".entry-form").fadeOut("fast");
        });

        $(".add-row").click(function(){
            
            var newchar = '';
            var i_idjmlrec = $("#e_idjmlrec").val();
            var i_jv = $("#cb_jv").val();
            var i_iddokt = $("#cb_doktid").val();
            var i_ket = $("#e_ketdetail").val();

            var x = document.getElementById("cb_doktid").selectedIndex;
            var y = document.getElementById("cb_doktid").options;
            var iidokt=y[x].index;
            var i_nmdokt=y[x].text;


            if (i_iddokt=="") {
                alert("dokter belum dipilih...!!!");
                return false;
            }

            var arjmlrec = document.getElementsByName('m_idjmrec[]');
            for (var i = 0; i < arjmlrec.length; i++) {
                var ijmlrec = arjmlrec[i].value;
                var ikddokt = document.getElementById('m_iddokt['+ijmlrec+']').value;
                
                if (i_iddokt==ikddokt) {
                    return false;
                }
            }
            var markup;
            markup = "<tr>";
            markup += "<td nowrap><input type='checkbox' name='record'>";
            markup += "<input type='hidden' id='m_idjmrec["+i_idjmlrec+"]' name='m_idjmrec[]' value='"+i_idjmlrec+"' Readonly>";
            markup += "<input type='hidden' id='m_iddokt["+i_idjmlrec+"]' name='m_iddokt["+i_idjmlrec+"]' value='"+i_iddokt+"'>";
            markup += "</td>";
            markup += "<td nowrap class='divnone'><input type='checkbox' name='chkbox_br[]' id='chkbox_br["+i_idjmlrec+"]' value='"+i_idjmlrec+"' checked></td>";
            
            markup += "<td><button type='button' class='btn btn-warning btn-xs' onclick=\"EditDataBarang('chkbox_br[]', '"+i_idjmlrec+"')\">Edit</button></td>";
            
            markup += "<td nowrap>" + i_jv + "<input type='hidden' id='m_jv["+i_idjmlrec+"]' name='m_jv["+i_idjmlrec+"]' value='"+i_jv+"'></td>";
            markup += "<td nowrap>" + i_nmdokt + "<input type='hidden' id='m_nmdokt["+i_idjmlrec+"]' name='m_nmdokt["+i_idjmlrec+"]' value='"+i_nmdokt+"'></td>";
            markup += "<td >" + i_ket + "<span hidden><textarea class='form-control' id='txt_ketdokt["+i_idjmlrec+"]' name='txt_ketdokt["+i_idjmlrec+"]'>"+i_ket+"</textarea></span></td>";
            markup += "</tr>";
            $("table tbody.inputdata").append(markup);
            
            
            if (i_idjmlrec=="") i_idjmlrec="0";
            i_idjmlrec = i_idjmlrec.split(',').join(newchar);
            i_idjmlrec=parseFloat(i_idjmlrec)+1;
            document.getElementById('e_idjmlrec').value=i_idjmlrec;

        });

        $(".delete-row").click(function(){
            
            var ilewat = false;
            $("table tbody.inputdata").find('input[name="record"]').each(function(){
                if($(this).is(":checked")){
                    $(this).parents("tr").remove();
                    ilewat = true;
                }
            });

            if (ilewat == true) {
                
            }
            
        });

    });


    function EditDataBarang(xchk, xidjmlrec) {
        var xkddokt = document.getElementById('m_iddokt['+xidjmlrec+']').value;
        var xkdjv = document.getElementById('m_jv['+xidjmlrec+']').value;
        
        var xket = document.getElementById('txt_ketdokt['+xidjmlrec+']').value;        
        
        document.getElementById("cb_jv").value = xkdjv;
        document.getElementById("cb_doktid").value = xkddokt;
        document.getElementById('e_ketdetail').value=xket;
        
        $("table tbody.inputdata").find('input[id="chkbox_br['+xidjmlrec+']"]').each(function(){
            $(this).parents("tr").remove();
        });
        
    }


    function disp_confirm(pText_,ket)  {
        
        var iid = document.getElementById('e_id').value;
        var ijmldata = document.getElementById('e_idjmlrec').value;
        var itgl = document.getElementById('e_periode1').value;
        var ikaryawan = document.getElementById('e_idcarduser').value;
        var ikeperluan = document.getElementById('cb_ketid').value;
        
        if (ikeperluan=="000") {
            if (ijmldata<=1) {
                alert("Dokter belum dipilih...");
                return false;
            }
        }

        $.ajax({
            type:"post",
            url:"module/dkd/viewdatadkd.php?module=cekdatasudahada",
            data:"uid="+iid+"&utgl="+itgl+"&ukaryawan="+ikaryawan,
            success:function(data){
                //var tjml = data.length;
                //alert(data);
                //return false;

                if (data=="boleh") {

                    ok_ = 1;
                    if (ok_) {
                        var r=confirm(pText_)
                        if (r==true) {
                            var myurl = window.location;
                            var urlku = new URL(myurl);
                            var module = urlku.searchParams.get("module");
                            var idmenu = urlku.searchParams.get("idmenu");
                            //document.write("You pressed OK!")
                            document.getElementById("form_data1").action = "module/dkd/dkd_wekvisitplan/aksi_wekvisitplan.php?module="+module+"&act="+ket+"&idmenu="+idmenu;
                            document.getElementById("form_data1").submit();
                            return 1;
                        }
                    } else {
                        //document.write("You pressed Cancel!")
                        return 0;
                    }

                }else{
                    alert(data);
                }
            }
        });
        
        
        
    }


</script>



<link href="css/inputselectbox.css" rel="stylesheet" type="text/css" />
<link href="css/stylenew.css" rel="stylesheet" type="text/css" />

<style>
    .form-group, .input-group, .control-label {
        margin-bottom:3px;
    }
    .control-label {
        font-size:12px;
    }
    input[type=text] {
        box-sizing: border-box;
        color:#000;
        font-size:12px;
        height: 30px;
    }
    select.soflow {
        font-size:12px;
        height: 30px;
    }
    .disabledDiv {
        pointer-events: none;
        opacity: 0.4;
    }
    .btn-primary {
        width:50px;
        height:30px;
        margin-right: 50px;
    }
    .disabledDiv {
        pointer-events: none;
        opacity: 0.4;
    }
</style>

<style>
    .divnone {
        display: none;
    }
    #datatablestockopn th {
        font-size: 13px;
    }
    #datatablestockopn td { 
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
}

.th2 {
    background: white;
    position: sticky;
    top: 23;
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
    border-top: 1px solid #000;
}
</style>


<script type="text/javascript">
    $(function() {
        var dateToday = new Date();
        var dayToday = dateToday.getDay();
        var setMinDate=7-dayToday;

        $('#e_periode1').datepicker({
            changeMonth: true,
            changeYear: true,
            numberOfMonths: 1,
            //firstDay: 1,
            //minDate: "1W",
            minDate: setMinDate, 
            //maxDate: "+2W -3D",
            dateFormat: 'dd MM yy',
            onSelect: function(dateStr) {
                
            }
        });

    });
</script>