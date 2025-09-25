<!DOCTYPE html>
<html>

<head>
 <title></title>
 <style type="text/css">
  body {
   font-family: "Arial", sans-serif;
   font-size: 12px;
  }

  #header {
   margin-top: 0px
  }

  p {
   margin: 0;
  }

  table {
   width: 100%;
   margin: 0 auto;
  }

  tr th {
   background: #eee;
  }

  .border-bottom {
   border-bottom: 1px solid black;
  }

  @media print {
   .page-break {
    page-break-before: always;
   }
  }

  caption {
   text-align: left;
  }

  img {
   margin-top: 10px;
  }

  .content-width {
   padding-right: 45px;
  }

  .footer-width {
   padding-right: 100px;
  }

  .footer-bottom {
   padding-bottom: 125px;
  }

  #header {
   margin-bottom: 10px;
   margin-top: -15px
  }
 </style>
</head>

<body>
 <!-- Header -->
 <div>
  <table>
   <tr>
    <td width="18%">
     <div>
      <img src="{{asset('images/logo-permata.png')}}" style="margin-left: 0px; width: 80%; height: 80%">
     </div>
    </td>

    <td width="72%" style="margin-top:-15px;">
     <p style="font-size: 18px;text-align: center;"><b>LABORATORIUM RUMAH SAKIT PERMATA CIREBON</b></p>
     <font size="3">
      <p align="center" style="font-size: 14px;">Jl. Tuparev No. 117, Cirebon 45153 - Telp (0231) 8338877 & (0231)
       8338899</p>
     </font>
    </td>
    <td width="10%"></td>
   </tr>
  </table>
 </div>

 <hr style="border: 1px solid black">

 <!-- Sub Header -->
 <table>
  <tr>
   <td style="text-align: center; font-size:15px;"><b><u> HASIL PEMERIKSAAN LABORATORIUM</u></b></td>
  </tr>
  <tr>
   <td style="text-align: right;">Cetakan ke : {{$transaction->print}}</td>
  </tr>
 </table>

 <!-- Sub Header 2 -->
 <table>
  <tbody>
   <tr>
    <td style="text-align: left;" width="17%"><b>Penanggungjawab</b></td>
    <td style="text-align: left;" width="83%">: dr. Rika Nilapsari, Sp. PK M.Pd, Ked</td>
   </tr>
  </tbody>
 </table>

 <hr>
 <br>

 <!-- Detail patient -->
 <table>
  <tbody>
   <tr>
    <td style="text-align: left; width: 17%;">Nama Pasien</td>
    <td style="text-align: left; width: 40%;">: <b> {{ $transaction->patient_name }} </b> </td>
    <td style="text-align: left; width: 15%;">Tanggal </b></td>
    <td style="text-align: left; width: 28%;">:
     <?= date('d/m/Y', strtotime($transaction->checkin_time)); ?>
    </td>

   </tr>
   <tr>
    <td style="text-align: left;">Tanggal Lahir / Umur</td>
    <td style="text-align: left;">:
     <?= date('d/m/Y',  strtotime($transaction->patient_birthdate)); ?> / {{$age}}
    </td>
    <td style="text-align: left;">Diagnosis</td>
    <td style="text-align: left;">: </td>
   </tr>
   <tr>
    <td style="text-align: left;">No. RM/ No Lab</td>
    <td style="text-align: left;">: <b>{{$transaction->patient_medrec}} / {{$transaction->no_lab}}</b></td>
    <td style="text-align: left;">Dokter Pengirim </td>
    <td style="text-align: left;">: {{ $transaction->doctor_name }}</td>
   </tr>
   <tr>
    <td style="text-align: left;">Alamat Pasien</td>
    <td style="text-align: left;">: {{ $transaction->patient_address }}</td>
    <td style="text-align: left;">Ruangan </td>
    <td style="text-align: left;">: {{ $transaction->room_name }}</td>
   </tr>
   <tr>
    <td style="text-align: left;">Jenis Kelamin </td>
    @if ($transaction->patient_gender == 'M')
    <td style="text-align: left;">: Laki-laki</td>
    @elseif ($transaction->patient_gender == 'F')
    <td style="text-align: left;">: Perempuan</td>
    @endif

    <td style="text-align: left;">Jenis Pasien </td>
    <td style="text-align: left;">: {{ $transaction->insurance_name }} </td>

   </tr>
  </tbody>
 </table>

 <hr style="border: 1px solid black">

 <!-- Thead TB Result -->
 <table id="tb_result" style="border-bottom: 1px solid black; width: 100%;">
  <thead>
   <tr>
    <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:25%;">JENIS PEMERIKSAAN
    </th>
    <th class="border-bottom" id="content" style="text-align: right; width:10%;">HASIL</th>
    <th class="border-bottom" id="content" style="width:15%;"></th>
    <th class="border-bottom" id="content" style="text-align: center; width:20%;">NILAI RUJUKAN</th>
    <th class="border-bottom" id="content" style="text-align: center; width:10%;">SATUAN</th>
    <th class="border-bottom" id="content" style="text-align: center; width:36%;">KETERANGAN</th>
   </tr>
  </thead>
  <tbody>
   <?php
                $group_test = '';
                $sub_group = '';
                $package_name = '';
                $new_page = 0;
                $row = 0;
                $page = 1; ?>
   @foreach ($tests as $test)
   <?php $hitung;
                ?>
   @if((($group_test != '') && ($group_test != $test->group_name) && (count($groups[$test->group_name]) + $row > 30)) ||
   ($row > 50))
   <?php $row = 0; ?>
  </tbody>
 </table>

 <hr>

 <!-- Page info -->
 <table>
  <tbody>
   <tr>
    <!--<td style="text-align: right;"> Hal.  {{ $page }}</td>-->
   </tr>
  </tbody>
 </table>

 <?php $page++; ?>

 <!-- Header page ++ -->
 <div id="header" class="page-break">
  <table>
   <tr>
    <td width="18%">
     <div>
      <img src="{{asset('images/logo-permata.png')}}" style="margin-left: 0px; width: 80%; height: 80%">
     </div>
    </td>
    <td width="72%" style="margin-top:-15px;">

     <p style="font-size: 18px;text-align: left;"><b>LABORATORIUM RUMAH SAKIT PERMATA CIREBON</b></p>
     <font size="3">
      <p align="center" style="font-size: 14px;">Jl. Tuparev No. 117, Cirebon 45153 - Telp (0231) 8338877 & (0231)
       8338899</p>
     </font>
    </td>
    <td width="10%"></td>
   </tr>
  </table>
 </div>

 <hr style="border: 1px solid black">

 <!-- Sub header page ++ -->
 <table>
  <tr>
   <td style="text-align: center;"><b><u> HASIL PEMERIKSAAN LABORATORIUM</u></b></td>
  </tr>
  <tr>
   <td style="text-align: right;">Cetakan ke : {{$transaction->print}}</td>
  </tr>
 </table>

 <!-- Sub header 2 page ++ -->
 <table>
  <tbody>
   <tr>
    <td style="text-align: left;" width="17%"><b>Penanggungjawab</b></td>
    <td style="text-align: left;" width="83%">: dr. Rika Nilapsari, Sp. PK</td>
   </tr>
  </tbody>
 </table>

 <!-- <hr style="border: 1px solid black"> -->
 <br>

 <!-- Detail patient page ++ -->
 <table>
  <tbody>
   <tr>
    <td style="text-align: left; width: 17%;">Nama Pasien</td>
    <td style="text-align: left; width: 40%;">: <b> {{ $transaction->patient_name }} </b> </td>
    <td style="text-align: left; width: 15%;">Tanggal </b></td>
    <td style="text-align: left; width: 28%;">:
     <?= date('d/m/Y', strtotime($transaction->checkin_time)); ?>
    </td>

   </tr>
   <tr>
    <td style="text-align: left;">Tanggal Lahir / Umur</td>
    <td style="text-align: left;">:
     <?= date('d/m/Y',  strtotime($transaction->patient_birthdate)); ?> / {{$age}}
    </td>
    <td style="text-align: left;">Diagnosis</td>
    <td style="text-align: left;">: </td>
   </tr>
   <tr>
    <td style="text-align: left;">No. RM/ No Lab</td>
    <td style="text-align: left;">: <b>{{$transaction->patient_medrec}} / {{$transaction->no_lab}}</b></td>
    <td style="text-align: left;">Dokter Pengirim </td>
    <td style="text-align: left;">: {{ $transaction->doctor_name }}</td>
   </tr>
   <tr>
    <td style="text-align: left;">Alamat Pasien</td>
    <td style="text-align: left;">: {{ $transaction->patient_address }}</td>
    <td style="text-align: left;">Ruangan </td>
    <td style="text-align: left;">: {{ $transaction->room_name }}</td>
   </tr>
   <tr>
    <td style="text-align: left;">Jenis Kelamin </td>
    @if ($transaction->patient_gender == 'M')
    <td style="text-align: left;">: Laki-laki</td>
    @elseif ($transaction->patient_gender == 'F')
    <td style="text-align: left;">: Perempuan</td>
    @endif

    <td style="text-align: left;">Jenis Pasien </td>
    <td style="text-align: left;">: {{ $transaction->insurance_name }} </td>

   </tr>
  </tbody>
 </table>

 <hr style="border: 1px solid black">

 <!-- Thead TB result page ++ -->
 <table id="tb_result" width="100%" style="border-bottom: 1px solid black;">
  <thead>
   <tr>
    <th class="border-bottom" id="content" style="text-align: left; padding-left:15px; width:25%;">JENIS PEMERIKSAAN
    </th>
    <th class="border-bottom" id="content" style="text-align: right; width:10%;">HASIL</th>
    <th class="border-bottom" id="content" style="width:15%;"></th>
    <th class="border-bottom" id="content" style="text-align: center; width:20%;">NILAI RUJUKAN</th>
    <th class="border-bottom" id="content" style="text-align: center; width:10%;">SATUAN</th>
    <th class="border-bottom" id="content" style="text-align: center; width:22%;">KETERANGAN</th>
   </tr>
  </thead>

  <?php $new_page = 1; ?>
  @endif

  <tbody>
   @if (($group_test == '') || ($group_test != $test->group_name))
   <?php $group_test = $test->group_name; ?>
   <tr id="content">
    <td style="padding-left:15px; padding-bottom: 5px;">
     <span style="font-size:10px; font-style: italic; font-weight: bold">
      {{ $test->group_name }}
     </span>
    </td>
    <td colspan="5"></td>
   </tr>
   <?php $row++; ?>
   @endif

   @if($test->print_package_name == 1)
   @if (($package_name == '') || ($package_name != $test->package_name))
   <?php $package_name = $test->package_name; ?>
   <tr id="content">
    <td style="padding-left:15px; padding-bottom: 5px;">
     <span style="font-size:10px; font-weight: bold">
      {{ $test->package_name }}
     </span>
    </td>
    <td colspan="5"></td>
   </tr>
   <?php $row++; ?>
   @endif
   @endif

   @if (($sub_group == '') || ($sub_group != $test->sub_group))
   @if ($test->sub_group != '')
   <tr id="content">
    <td style="padding-left:25px; padding-bottom: 3px;">
     <span style="font-size:10px; font-weight: bold">
      {{ $test->sub_group }}
     </span>
    </td>
    <td colspan="5"></td>
   </tr>
   <?php $row++; ?>
   @endif
   <?php $sub_group = $test->sub_group; ?>
   @endif

   <tr id="content">
    <!-- Test name -->
    <td id="content" style="padding-left:35px; padding-bottom: 1px;">
     {{ $test->test_name }}
    </td>
    <?php
     $result = $test->global_result;
     if (is_string($test->global_result)) {
         $result = $test->global_result;
     }
    ?>

    <!-- Result -->
    @if (strlen($test->global_result) > 15)
    @if (($test->test_name == 'Gambaran Darah Tepi') || ($test->normal_value == '' && $test->unit == ''))
    <td colspan="5" id="content" style="text-align: left; padding-bottom: 3px;">
     {!! $result !!}
    </td>
    @else
    <td id="content" style="text-align: right; padding-bottom: 3px;">
     {!! $result !!}
    </td>
    <!-- Flagging tetap ada -->
    <td id="content" style="text-align: left; padding-bottom: 3px;
            @if ($test->result_status_label == 'Critical') color:red; font-weight:bold; @endif">
     @if ($test->result_status_label == 'Critical')
     **
     @elseif ($test->result_status_label == 'Abnormal')
     *
     @endif
    </td>
    <td id="content" style="text-align: center; padding-bottom: 3px;">
     {!! $test->normal_value !!}
    </td>
    <td id="content" style="text-align: center; padding-bottom: 3px;">
     {{ $test->unit }}
    </td>
    <td id="content" style="text-align: left; padding-bottom: 3px;">
     <?php
              if ($test->result_status_label == 'Critical') {
                  echo 'Hasil kritis dilaporkan oleh ' . $test->report_by .
                       ' kepada ' . $test->report_to .
                       ' (' . date('H:i', strtotime($test->report_time)) . ')<br>';
                  echo '<b>Keterangan : </b>' . $test->memo_test;
              } elseif($test->memo_test) {
                  echo $test->memo_test;
              }
         ?>
    </td>
    @endif
    @else
    <td id="content" style="text-align: right; padding-bottom: 3px;">
     {!! $result !!}
    </td>
    <!-- Flagging tetap ada -->
    <td id="content" style="text-align: left; padding-bottom: 3px;
          @if ($test->result_status_label == 'Critical') color:red; font-weight:bold; @endif">
     @if ($test->result_status_label == 'Critical')
     **
     @elseif ($test->result_status_label == 'Abnormal')
     *
     @endif
    </td>
    <td id="content" style="text-align: center; padding-bottom: 3px;">
     {!! $test->normal_value !!}
    </td>
    <td id="content" style="text-align: center; padding-bottom: 3px;">
     {{ $test->unit }}
    </td>
    <td id="content" style="text-align: left; padding-bottom: 3px;">
     <?php
            if ($test->result_status_label == 'Critical') {
                echo 'Hasil kritis dilaporkan oleh ' . $test->report_by .
                     ' kepada ' . $test->report_to .
                     ' (' . date('H:i', strtotime($test->report_time)) . ')<br>';
                echo '<b>Keterangan : </b>' . $test->memo_test;
            } elseif($test->memo_test) {
                echo $test->memo_test;
            }
       ?>
    </td>
    @endif

    <!-- Special case: Urine test id 659 -->
    @if($test->test_id == 659)
    <td id="content" style="text-align: left;" rowspan="2">
     Kuantifikasi Bakteri : <br>
     +1 ( <10 /Seluruh LP ) <br>
      +2 ( 1-6/LP ) <br>
      +3 ( 6-30/LP ) <br>
      +4 ( >30/LP ) <br><br>
      Kuantifikasi Leukosit & Epitel: <br>
      +1 ( <2 /LPK ) <br>
       +2 ( 2-9/LPK ) <br>
       +3 ( 10-24/LPK ) <br>
       +4 ( >25/LPK )
    </td>
    @endif
   </tr>

   <?php $row++; ?>
   @endforeach
  </tbody>
 </table>

 <br>

 <table>
  <tbody>
   @if($transaction->is_print_memo != '')
   <tr>
    <td style="text-align: left;">Keterangan :</td>
   </tr>
   <tr>
    <td style="text-align: left;">
     <?php
      if ($transaction->memo_result == 'hivpos') {
       $keterangan = [];
       if (($open = fopen(storage_path() . "/csv/hivpos.csv", "r")) !== FALSE) {
           while (($data = fgetcsv($open, ".")) !== FALSE) {
               $keterangan[] = $data;
           }
           fclose($open);
       }
       foreach ($keterangan as $value) {
           // Mengganti titik dengan titik diikuti oleh tag <br>
           $text_with_line_breaks = str_replace('.', '.<br>', $value[0]);
           echo $text_with_line_breaks . "<br>";
       }
      } else if ($transaction->memo_result == 'negatif') {
       $keterangan = [];
       if (($open = fopen(storage_path() . "/csv/negatif.csv", "r")) !== FALSE) {
           while (($data = fgetcsv($open, ".")) !== FALSE) {
               $keterangan[] = $data;
           }
           fclose($open);
       }
       foreach ($keterangan as $value) {
           // Mengganti titik dengan titik diikuti oleh tag <br>
           $text_with_line_breaks = str_replace('.', '.<br>', $value[0]);
           echo $text_with_line_breaks . "<br>";
       }
      } else if ($transaction->memo_result == 'positif') {
       $keterangan = [];
       if (($open = fopen(storage_path() . "/csv/positif.csv", "r")) !== FALSE) {
           while (($data = fgetcsv($open, ".")) !== FALSE) {
               $keterangan[] = $data;
           }
           fclose($open);
       }
       foreach ($keterangan as $value) {
           // Mengganti titik dengan titik diikuti oleh tag <br>
           $text_with_line_breaks = str_replace('.', '.<br>', $value[0]);
           echo $text_with_line_breaks . "<br>";
       }
      } else {
       // Mengganti titik dengan tag <br>
      // Mengganti titik dengan titik diikuti oleh tag <br>
      $text_with_line_breaks = str_replace('.', '.<br>', $transaction->memo_result);
      $text_with_line_breaks = str_replace(':', ':<br>', $text_with_line_breaks);
      echo $text_with_line_breaks;
     } ?>
    </td>
   </tr>
   @endif
  </tbody>
 </table>

 <br>

 <table>
  <tbody>
   <tr>
    <td style="text-align: left; width: 19%;"> Jam Pengambilan Sample </td>
    <td style="text-align: left; width: 20%;"> :
     <?= date('d/m/Y H:i', strtotime($transaction->checkin_time)); ?>
    </td>
    <td style="text-align: left; width: 25%;"> </td>
    <td style="text-align: left; width: 25%;"> </td>
   </tr>
   <tr>
    <td style="text-align: left; width: 19%;"> Jam Cetak Hasil </td>
    <td style="text-align: left; width: 20%;"> :
     <?= date('d/m/Y H:i', strtotime($transaction->post_time)); ?>
    </td>
    <td style="text-align: left; width: 25%;"> </td>
    <td style="text-align: left; width: 25%;"> </td>
   </tr>
  </tbody>
 </table>

 <table style="position: fixed;">
  <tbody>
   <tr>
    <td width="70%" style="text-align: right;"> </td>
    <td width="30%" style="text-align: center;">Pemeriksa,<br><br><br><br></td>
   </tr>
   <tr>
    <td width="70%" style="text-align: right;"> </td>
    <td width="30%" style="text-align: center;">dr. Rika Nilapsari, Sp. PK, M. Pd, Ked</td>
   </tr>
  </tbody>
 </table>

</body>

</html>