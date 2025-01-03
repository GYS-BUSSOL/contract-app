<script setup>
import dayjs from "dayjs";
import 'vue-skeletor/dist/vue-skeletor.css';

const isLoadingSPK = ref(true)
const allDataContract = ref([])
const allUserSignature = ref([])
const shiftData = ref()
const rangeType = ref([])
const countShiftData = ref()
const userAuth = useCookie('userData')
const token = useCookie('accessToken')
const route = useRoute('apps-pbl-preview-id')
const SPKId = computed({
  get: () => route.params.id,
  set: () => route.params.id,
})

const vendorData = reactive({
  vnd_name: null,
  vnd_contact_person: null,
  vnd_address: null,
})

const signType = reactive({
  st_user: null,
  st_id: null,
})

const userData = reactive({
  usr_display_name: null,
  usr_jabatan: null,
})

const companyData = reactive({
  company_alamat: null,
  company_name: null,
})

const SPKData = reactive({
  spk_jobdesc_summary: null,
  durations: null,
  signature_type: null,
  spk_date: null,
  spk_web_id: null,
  spk_no: null,
  ven_id: null,
  spk_renewal_box: null,
  spk_start_date: null,
  spk_end_date: null,
  spk_box_bpjs: null,
  spk_cara_bayar: null,
  spk_tahap_bayar: null,
  spk_lain_lain: null,
})

const IDRFormat = (data) => {
  return 'Rp. ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data).replace('Rp', '').trim()
}

const fetchSPKEdit = async () => {
  try {
    isLoadingSPK.value = true;
    const response = await $api(`/apps/spk/edit/${SPKId.value}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      onResponseError({ response }) {
        const responseData = response._data;
        const responseMessage = responseData.message;
        const responseErrors = responseData.errors;
        emit('errors', responseErrors);
        emit('errorMessages', responseMessage);
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      isLoadingSPK.value = false;
      const dataResult = dataResponse.data;
      // SPK
      SPKData.spk_web_id = dataResult.spk_web_id;
      SPKData.ven_id = dataResult.ven_id;
      SPKData.spk_jobdesc_summary = dataResult.spk_jobdesc_summary;
      SPKData.spk_start_date = dataResult.spk_start_date;
      SPKData.spk_end_date = dataResult.spk_end_date;
      SPKData.spk_box_bpjs = dataResult.spk_box_bpjs;
      // User Signature
      allUserSignature.value = dataResult.userSignature;
      // Shift
      countShiftData.value = Number(dataResult.countShift.jml_shift);
      // Vendor
      vendorData.vnd_name = dataResult.vnd_name;
      vendorData.vnd_contact_person = dataResult.vnd_contact_person;
      vendorData.vnd_address = dataResult.vnd_address;
      // Sign type
      signType.st_id = dataResult.st_id;
      signType.st_user = dataResult.st_user;
      // User
      userData.usr_display_name = dataResult.usr_display_name;
      userData.usr_jabatan = dataResult.usr_jabatan;
      // Company
      companyData.company_name = dataResult.company_name;
      companyData.company_alamat = dataResult.company_alamat;
      // Contract
      allDataContract.value = dataResult.contract;
    } else {
      throw new Error("Get data failed");
    }
  } catch (error) {
    throw new Error("Get data failed");
  }
}

const updatePrintData = async () => {
  const SPKDataNew = JSON.parse(localStorage.getItem('SPKData'))
  const rangeNew = JSON.parse(localStorage.getItem('range'))
  const shiftDataNew = localStorage.getItem('shiftData')

  SPKData.spk_no = SPKDataNew.spk_no
  SPKData.spk_renewal_box = SPKDataNew.spk_renewal_box
  SPKData.spk_date = SPKDataNew.spk_date
  SPKData.spk_cara_bayar = SPKDataNew.spk_cara_bayar
  SPKData.spk_tahap_bayar = SPKDataNew.spk_tahap_bayar
  SPKData.spk_lain_lain = SPKDataNew.spk_lain_lain
  shiftData.value = shiftDataNew
  rangeType.value = rangeNew[0][0]
}

onMounted(async () => {
  if(SPKId.value) {
    await fetchSPKEdit()
    await updatePrintData()
    setInterval(()=> {
      window.print()
    },1000)
  }
});
</script>

<template>
  <VRow>
    <VCol cols="12" v-if="isLoadingSPK" class="mt-5">
      <Skeletor height="100vh" />
    </VCol>
    <VCol cols="12" v-if="!isLoadingSPK">
      <VCard class="invoice-preview-wrapper pa-6 pa-sm-12">
        <table width="100%">
          <!-- Letter date -->
          <thead>
            <tr>
              <td>
                <img
                  src="../../../../../../public/GYS-Logo.png"
                  width="15%"
                  height="auto"
                  alt="bg-img"
                  class="custom-checkbox-image"
                >
              </td>
            </tr>
            <tr>
              <td>
                <hr class="my-6"/>
              </td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <table width="100%">
                  <tbody>
                    <tr align="right">
                      <span style="float:right;">Cikarang Barat, {{ dayjs(SPKData.spk_date).format('YYYY-MM-DD') }}</span>
                    </tr>
                  </tbody>
                </table>
                <table width="100%">
                  <tr>
                    <th colspan="2" style="text-align:center">
                      <strong><u>SURAT PERINTAH KERJA</u></strong>
                    </th>
                  </tr>
                  <tr>
                    <td colspan="2" style="text-align:center">NO : 
                      {{ SPKData.spk_no }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" style="text-align:center">
                      <strong>{{ SPKData.spk_renewal_box }}</strong>
                    </td>
                  </tr>
                  <tr>
                    <td>Dengan ini kami /
                      <i style="font-size: 12px;">Hereby we : </i>    
                    </td>
                  </tr>
                  <tr>
                    <td>Nama / 
                      <i style="font-size: 12px;">Name</i>
                    </td>
                    <td>:
                      {{ userData.usr_display_name || '-' }}
                    </td>
                  </tr>
                  <tr>
                    <td>Jabatan /
                    <i style="font-size: 12px;">Position</i>    
                    </td>
                    <td>: 
                      {{ userData.usr_jabatan || '-' }} 
                    </td>
                  </tr>
                  <tr>
                    <td>Alamat /
                      <i style="font-size: 12px;">Address</i>    
                    </td>
                    <td>:
                      {{ companyData.company_alamat || '-' }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2">
                      Selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong> <i style="font-size: 12px;">/ Hereinafter referred to as the <strong>FIRST PARTY</strong></i>
                    </td>
                  </tr>
                  <tr>
                    <td style="display: inline-block; white-space: nowrap;">
                      Memberikan Perintah Kerja kepada / <span style="font-size: 12px;"><i>To issue work order to</i></span> :
                    </td>
                  </tr>
                  <tr>
                    <td>Nama / 
                      <i style="font-size: 12px;">Name</i>    
                    </td>
                    <td>:
                      {{ vendorData.vnd_contact_person || '-' }}
                    </td>
                  </tr>
                  <tr>
                    <td>Jabatan /
                      <i style="font-size: 12px;">Position</i>    
                    </td>
                    <td>: 
                      Direktur {{ vendorData.vnd_name || '-' }}
                    </td>
                  </tr>
                  <tr>
                    <td>Alamat /
                      <i style="font-size: 12px;">Address</i>
                    </td>
                    <td>:
                      {{ vendorData.vnd_address || '-' }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2">Selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong><i style="font-size: 12px;"> / Hereinafter referred to as the <strong>SECOND PARTY</strong></i><br></td>
                  </tr>
                  <tr>
                    <td>Untuk melaksanakan pekerjaan /
                      <i style="font-size: 12px;">To carry out the work:</i>    
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" style="text-align: center;">
                      <strong>"{{ SPKData.spk_jobdesc_summary || '-' }}"</strong>
                    </td>
                  </tr>
                  <template v-if="allDataContract" v-for="(c, indexC) in allDataContract">
                    <tr>
                      <td>Waktu pelaksanaan /
                        <i style="font-size: 12px;">Execution time</i>    
                      </td>
                      <td>:
                        {{ dayjs(SPKData.spk_start_date).format('DD MMMM YYYY') }} S/d {{ dayjs(SPKData.spk_end_date).format('DD MMMM YYYY') }}
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <strong>Business Unit</strong>
                      </td>
                      <td>: 
                        <strong>
                          {{ c.data.con_bu + ' - ' + c.data.description }}
                        </strong>
                      </td>
                    </tr>
                    <tr>
                      <td>Sifat Perintah Kerja /
                        <i style="font-size: 12px;">Type Of Work Order</i>    
                      </td>
                      <td>: 
                        {{ c.data.con_priority_id == '1' ? 'Segera' : 'Tidak Segera' }}
                      </td>
                    </tr>
                    <tr>
                      <td>Jam Kerja /
                        <i style="font-size: 12px;">Working Hour</i>    
                      </td>
                      <td>:
                        {{ shiftData }}
                      </td>
                    </tr>
                    <tr>
                      <td>ID Project</td>
                      <td>: 
                        {{ c.data.con_id_project || '-' }}
                      </td>
                    </tr>                                
                    <tr>
                      <td>Keterangan /
                        <i style="font-size: 12px;">Description : </i>    
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">- Cost Centre : 
                        {{ c.cc.map((dataCC) => dataCC.tbc_code + ' - ' + dataCC.description).join(' ,') || '-' }}
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">- Work Centre : 
                        {{ c.wc.map((dataWC) => dataWC.tbc_code + ' - ' + dataWC.description).join(' ,') || '-' }}
                      </td>
                    </tr>
                    <tr>
                      <td>- PPS Number : 
                        {{ c.data.con_pps_no || '-' }}
                      </td>
                    </tr>
                    <!-- Contract Job -->
                    <template v-if="c.contractJob && c.contractJob.length > 0" v-for="(cj, indexCJ) in c.contractJob" :key="cj.data.cjb_id">
                      <tr>
                        <td colspan="2">
                          <tr>
                              <td colspan="2">
                                <strong>Pekerjaan / <i style="font-size: 12px;">Work</i> : 
                                  {{ SPKData.spk_jobdesc_summary || '-' }}
                                </strong>        
                              </td>
                          </tr>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <!-- Mandays -->
                          <table class="table table-bordered" v-if="cj.data.cjb_pay_template === 'mandays'">
                            <tbody>
                              <tr>
                                <td>Tenaga Kerja</td>
                                <td>100% <span class="required" aria-required="true">*</span></td>
                                <td>108% <span class="required" aria-required="true">*</span></td>
                                <td>Tagihan 105% <span class="required" aria-required="true">*</span></td>
                                <td>Retensi 3% <span class="required" aria-required="true">*</span></td>
                              </tr>
                              <tr v-for="(rate, rateIndex) in cj.rate" :key="rate.rtk_id">
                                <td><strong>{{ rate.rtk_id_jenis_tk + ' = ' + parseInt(rate.cjl_qty) + ' Orang' }}</strong></td>
                                <td>{{ IDRFormat(parseInt(rate.rtk_rate)) + ' /Org/Hari' }}</td>
                                <td>{{ IDRFormat(parseInt(rate.rtk_rate * 1.08)) + ' /Org/Hari' }}</td>
                                <td>{{ IDRFormat(parseInt(rate.rtk_rate * 1.05)) + ' /Org/Hari' }}</td>
                                <td>{{ IDRFormat(parseInt(rate.rtk_rate * 0.03)) + ' /Org/Hari' }}</td>
                              </tr>
                            </tbody>
                          </table>
                          <!-- Fixed & Flat2 -->
                          <div style="text-align: left;" v-if="cj.data.cjb_pay_template === 'fixed' || cj.data.cjb_pay_template === 'flat2'">
                            Nilai Harga Borongan / <i style="font-size: 12px;">Wholesale Price Value</i> : 
                            {{ IDRFormat(cj.data.cjb_rate) + ",- /" +  cj.data.cjb_pay_type }} 
                            <span v-if="cj.data.cjb_pay_type == 'kg'">
                              <i>(Dikontrol berdasarkan timbangan)</i>
                            </span>
                          </div>
                          <!-- Flat -->
                          <div style="text-align: left;" v-if="cj.data.cjb_pay_template === 'flat'">
                            Nilai Biaya / <i style="font-size: 12px;">Cost Value</i> : 
                            {{ IDRFormat(cj.data.cjb_rate) + ",- /" +  cj.data.cjb_pay_type }} 
                            <span v-if="cj.data.cjb_pay_type == 'kg'">
                              <i>(Dikontrol berdasarkan timbangan)</i>
                            </span>
                          </div>
                          <!-- P1 & Rit -->
                          <table class="table table-bordered mt-5" v-if="cj.data.cjb_pay_template === 'p1' || cj.data.cjb_pay_template === 'rit'">
                            <tbody>
                              <tr>
                                <td>100% <span class="required" aria-required="true">*</span></td>
                                <td>108% <span class="required" aria-required="true">*</span></td>
                                <td>Tagihan 105% <span class="required" aria-required="true">*</span></td>
                                <td>Retensi 3% <span class="required" aria-required="true">*</span></td>
                              </tr>
                              <tr>
                                <td>{{ IDRFormat(parseInt(cj.data.cjb_rate)) + ' /' + cj.data.cjb_pay_type }}</td>
                                <td>{{ IDRFormat(parseInt(cj.data.cjb_rate * 1.08)) + ' /' + cj.data.cjb_pay_type }}</td>
                                <td>{{ IDRFormat(parseInt(cj.data.cjb_rate * 1.05)) + ' /' + cj.data.cjb_pay_type }}</td>
                                <td>{{ IDRFormat(parseInt(cj.data.cjb_rate * 0.03)) + ' /' + cj.data.cjb_pay_type }}</td>
                              </tr>
                            </tbody>
                          </table>
                          <!-- Range -->
                          <table class="table table-bordered mt-5" v-if="cj.data.cjb_pay_template === 'range'  && cj.range && cj.range.length > 0">
                            <thead>
                              <tr>
                                <th>Minimal Produksi</th>
                                <th>Maksimal Produksi</th>
                                <th>UoM</th>
                                <th>Harga (Rp)</th>
                                <th>Maksimal Perhitungan</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(r, indexCJ) in rangeType" :key="r.id">
                                <td>{{ r.min_produksi || 1 }}</td>
                                <td>{{ r.max_produksi || 2000 }}</td>
                                <td>{{ r.uom || '-' }}</td>
                                <td>{{ r.harga || '-' }}</td>
                                <td>{{ r.max_batas || '-' }}</td>
                              </tr>
                            </tbody>
                          </table>
                          <p class="mt-5">Job Target : {{ c.data.con_comment_jobtarget || '-' }}</p>
                        </td>
                      </tr>
                      <tr v-if="cj.data.cjb_pay_template === 'mandays' || cj.data.cjb_pay_template === 'p1' || cj.data.cjb_pay_template === 'rit'">
                        <td colspan="2">
                          Retensi dibayar setiap 6 (enam) bulan sekali dengan dasar penilaian (terlampir)
                        </td>
                      </tr>
                    </template>
                  </template>
                </table>
                <table width="100%">
                  <tbody>
                    <tr>
                      <td width="25%" style="vertical-align: top">Cara Pembayaran :
                        <br><i style="font-size: 12px;">(Payment Method)</i>    
                      </td>
                      <td v-html="SPKData.spk_cara_bayar"></td>
                    </tr>
                    <tr>
                      <td width="25%" style="vertical-align: top">Tahapan Pembayaran :
                        <br><i style="font-size: 12px;">(Payment Stages)</i>    
                      </td>
                      <td v-if="SPKData.spk_tahap_bayar != null && SPKData.spk_tahap_bayar != ''" v-html="SPKData.spk_tahap_bayar"></td>
                      <td v-else>Payment will be made within 7 days after the progress & invoice are received by {{ companyData.company_name || '-' }}.</td>
                    </tr>
                  </tbody>
                </table>
                <table width="100%">
                  <tbody>
                    <tr>
                      <td>
                        <br>Lain-lain / <i style="font-size: 12px;">Other : </i>
                        <p v-if="SPKData.spk_lain_lain != null && SPKData.spk_lain_lain != ''" v-html="SPKData.spk_lain_lain"></p>
                        <p v-else>
                          - In the event of a work accident, responsibility shall lie with the Second Party
                          - All employees of the Second Party working within the premises of PT Garuda Yamato Steel are required to wear complete personal protective equipment (PPE) according to the work being performed. This includes a blue helmet, blue shirt uniform, safety shoes, mask, welding helmet/goggles, gloves, and other necessary equipment
                          - Meal allowances are covered by the Second Party
                          - Income tax is borne by the Second Party
                          - This Work Order can be extended or canceled if necessary
                          - Pricing calculations shall be reviewed and analyzed by the HR Department
                          - When billing, proof of payment for BPJS Ketenagakerjaan and BPJS Kesehatan from the previous month must be attached
                        </p>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <table width="100%">
                  <tr>
                    <td colspan="4">
                      <br>Demikian Surat Perintah Kerja ini dibuat dan agar dapat digunakan sebagaimana mestinya.<br>
                      <i style="font-size: 12px;">(This work order letter is hereby made and should be used accordingly)</i><br><br>
                    </td>
                  </tr>
                  <tr>
                    <td style="width: 25%">Hormat Kami / <i style="font-size: 12px;">Yours sincerely</i></td>
                    <td colspan="4"></td>
                  </tr>
                  <tr>
                    <td style="width: 25%">Penerima SPK / <i style="font-size: 12px;">SPK Recipient</i></td>
                    <td style="display: inline-block; white-space: nowrap; margin-left: 10px;">Diketahui / <span style="font-size: 12px;"><i>Acknowledged by</i></span></td>
                    <td colspan="2" align="center">Pemberi SPK / <i style="font-size: 12px;">SPK Giver</i></td>
                  </tr>
                  <tr>
                    <td style="width: 25%"><strong>{{ vendorData.vnd_name || '-' }}</strong></td>
                    <td style="width: 18%"></td>
                    <td colspan="2" align="center"><strong>{{ companyData.company_name || '-' }}</strong></td>
                  </tr>
                  <tr>
                    <td></td>
                    <td><br><br><br></td>
                    <td></td>
                  </tr>
                  <tr v-if="signType.st_id">
                    <td style="width: 25%; font-size:12px;" valign="top">
                      <u>{{ vendorData.vnd_contact_person || '-' }}</u><br>Pemborong
                    </td>
                    <td style="width: 18%; font-size:12px; padding-left: 10px;" valign="top">
                      <u>Bag. Pembelian</u>
                    </td>
                    <td style="font-size: 12px;" width="20%;" valign="top" align="center" v-for="(user, userIndex) in allUserSignature">
                      <u>{{ user.usr_display_name || '-' }}</u><br>{{ user.usr_jabatan }}
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td>
                <div style="bottom: 0;">
                  <table class="table table-bordered" style="margin-top: 20px;" height="80%">
                    <tbody>
                        <tr>
                          <td style="font-size:6;">The company does not collect any fees from SPK recipients. If any party attempts to collect fees, SPK recipients should immediately send a letter to cs@gyssteel.com and send SMS/call to mobile numbers 0813 828 50 888 and 688 02 988 or contact phone number (021)70853177 via SMS. SPK recipients are PROHIBITED from giving anything in any form to employees of PT Garuda Yamato Steel and their families.</td>
                        </tr>
                        <tr>
                          <td style="font-size:6;">If these above provisions are violated, the company will not make payment to the SPK recipient (Black List).</td>
                        </tr>
                    </tbody>
                  </table>
                  <p class="mt-5">
                    <small>Created by: {{ userAuth.usr_display_name }} versi {{ dayjs().format(`DD/M/YYYY`) }}</small>
                  </p>
                </div>
              </td>
            </tr>
          </tfoot>
        </table>
      </VCard>
    </VCol>
  </VRow>
</template>


<style lang="scss">
.invoice-preview-table {
  --v-table-header-color: var(--v-theme-surface);

  &.v-table .v-table__wrapper table thead tr th {
    border-block-end: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
  }
}

@page {
  size: A4;
  margin-left: 5%;
  margin-right: 5%;
  margin-top: 3%;
  margin-bottom: 3%;
}

@page:first {
  margin-top: 0.4%;
}


table {
  width: 100%;
  border-collapse: collapse;
}

.table-bordered th,
.table-bordered td {
  padding: 10px;
}

.table-bordered th,
.table-bordered td,
hr {
  border: 1px solid !important;
}


@media print {
  * {
    color: black !important;
    font-family: 'Times New Roman', Times, serif;
    font-size: 12px !important;
  }

  hr {
    border: 1px solid black !important;
  }

  .v-theme--dark {
    --v-theme-surface: 255, 255, 255;
    --v-theme-on-surface: 47, 43, 61;
    --v-theme-on-background: 47, 43, 61;
  }

  body {
    background: none !important;
    font-size: 12px !important;
  }

  .invoice-header-preview,
  .invoice-preview-wrapper {
    padding: 0 !important;
  }

  .product-buy-now {
    display: none;
  }

  .v-navigation-drawer,
  .layout-vertical-nav,
  .app-customizer-toggler,
  .layout-footer,
  .layout-navbar,
  .layout-navbar-and-nav-container {
    display: none;
  }

  .v-card {
    box-shadow: none !important;
  }

  .layout-content-wrapper {
    padding-inline-start: 0 !important;
  }

  .v-table__wrapper {
    overflow: hidden !important;
  }

  .vue-devtools__anchor {
    display: none;
  }
}
</style>
