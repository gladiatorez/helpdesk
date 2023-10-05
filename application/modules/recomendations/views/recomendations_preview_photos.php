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

<table cellpadding="5" cellspacing="10" style="width: 100%">
    <tr>
<?php 
    $photos = ($row['photos']) ? $row['photos'] : [];
    $col = 2;
    $cnt = 0;
    foreach ($photos as $photo) {
        $query = $this->db->select('filename')
            ->where('id', $photo->file)
            ->limit(1)
            ->get('files');
        $photoFile = $query->row();

        if ($cnt >= $col) {
            echo "</tr><tr>";
            $cnt = 0;
        }
        $cnt++; ?>
        <td style="border: 1px solid #000; text-align: center">
            <img src="<?php echo APPPATH . '../uploads/files/'.$photoFile->filename; ?>" width="9cm" style="margin-bottom: 10px" />
            <div style="text-align: left">
                <?php echo $photo->description; ?>
            </div>
        </td>
        <?php 
    } 
?>
    </tr>
</table>