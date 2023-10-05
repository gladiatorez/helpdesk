<style>
.table-heading {
    margin-bottom: 30px;
}
.table-heading td {
    padding: 3px 5px;
    vertical-align: top;
    line-height: 20px;
}
.table-approve {
    border: 1px solid #000;
    border-collapse: collapse;
    font-size: 10pt;
}
.table-approve td {
    border: 1px solid #000;
    text-align: center;
    padding: 5px 10px;
}
.font-10pt {
    font-size: 10pt;
}
.font-weight-bold {
    font-weight: bold;
}
.text-align-left {
    text-align: left;
}
.bg-blue {
    background-color: #C8E6C9;
}
blockquote {
    font-style: italic;
	padding: 2px 0;
	padding-left: 20px;
	font-family: Georgia, Times, "Times New Roman", serif;
    border-left: 5px #ccc solid;
}
.level1 { box-decoration-break: slice; }
.level2 { box-decoration-break: clone; }
.level3 { box-decoration-break: clone; }
</style>

<table class="table-heading font-10pt">
    <tr>
        <td>No</td>
        <td>:</td>
        <td><?= $row['letter_no'] . $row['letter_no_suffix'] ?></td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td><?= indoDate(strtotime($row['letter_date']), 'd F Y') ?></td>
    </tr>
    <tr>
        <td>Subject</td>
        <td>:</td>
        <td><?= $row['letter_subject'] ?></td>
    </tr>
    <tr>
        <td>User</td>
        <td>:</td>
        <td>
            <?= $row['ticket_informer_full_name']; ?><br/>
            <?php 
                $arr = [];
                if ($row['ticket_company_name']) {
                    $arr[] = $row['ticket_company_name'];
                }
                if ($row['ticket_company_branch_name']) {
                    $arr[] = $row['ticket_company_branch_name'];
                }
                if ($row['ticket_department_name']) {
                    $arr[] = $row['ticket_department_name'];
                }
                echo implode(' - ', $arr);
            ?>
        </td>
    </tr>
    <tr>
        <td>No. Tiket</td>
        <td>:</td>
        <td>#<?= $row['ticket_number'] ?></td>
    </tr>
    <tr>
        <td>Lampiran</td>
        <td>:</td>
        <td><?= $row['letter_attach'] > 0 ? $row['letter_attach'] . ' lembar' : '---' ?></td>
    </tr>
</table>

<table style="border: 1px solid #000; margin-bottom: 30px; width: 100%">
    <tr>
        <th style="padding:10px; background: #EEEEEE; font-size: 10pt; text-align:left">
        A. Latar Belakang
        </th>
    </tr>
    <tr>
        <td style="padding: 10px;">
        <?= $row['descr_background'] ?>
        </td>
    </tr>
</table>

<table style="border: 1px solid #000; margin-bottom: 30px; width: 100%">
    <tr>
        <th style="padding:10px; background: #EEEEEE; font-size: 10pt; text-align:left">
        B. Pemeriksaan
        </th>
    </tr>
    <tr>
        <td style="padding: 10px;">
        <?= $row['descr_examination'] ?>
        </td>
    </tr>
</table>

<table style="border: 1px solid #000; margin-bottom: 30px; width: 100%">
    <tr>
        <th style="padding:10px; background: #EEEEEE; font-size: 10pt; text-align:left">
        C. Penanganan Masalah
        </th>
    </tr>
    <tr>
        <td style="padding: 10px;">
        <?= $row['descr_handling'] ?>
        </td>
    </tr>
</table>

<table style="border: 1px solid #000; margin-bottom: 30px; width: 100%">
    <tr>
        <th style="padding:10px; background: #EEEEEE; font-size: 10pt; text-align:left">
        D. Hasil
        </th>
    </tr>
    <tr>
        <td style="padding: 10px;">
        <?= $row['descr_results'] ?>
        </td>
    </tr>
</table>

<table style="border: 1px solid #000; margin-bottom: 30px; width: 100%">
    <tr>
        <th style="padding:10px; background: #EEEEEE; font-size: 10pt; text-align:left">
            E. Rekomendasi
        </th>
    </tr>
    <tr>
        <td style="padding: 10px;">
            <?= $row['descr_recomendation'] ?>
        </td>
    </tr>
</table>

<table style="width: 100%;">
    <tr>
        <td>&nbsp;</td>
        <td style="text-align: right">
            <table class="table-approve">
                <tr>
                    <td class="bg-blue font-weight-bold">Disetujui Oleh :</td>
                    <td class="bg-blue font-weight-bold">Dibuat Oleh :</td>
                </tr>
                <tr>
                    <td style="height: 90px;">&nbsp;</td>
                    <td style="height: 90px;">&nbsp;</td>
                </tr>
                <tr>
                    <td><?= $row['approve_full_name'] ?></td>
                    <td><?= $row['maker_full_name'] ?></td>
                </tr>
                <tr>
                    <td class="bg-blue"><?= $row['approve_position'] ?></td>
                    <td class="bg-blue"><?= $row['maker_position'] ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>