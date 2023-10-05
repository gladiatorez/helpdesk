<table border="1" cellpadding="2" cellspacing="0">
  <tr>
    <th>Number</th>
    <th>Subject</th>
    <th>Description</th>
    <th>Company</th>
    <th>Location</th>
    <th>Owner</th>
    <th>Date Opened</th>
    <th>Date Closed</th>
    <th>Status</th>
    <th>Category Head</th>
    <th>Category Sub</th>
    <th>Staff</th>
    <th>SLA (Hours)</th>
    <th>Duration</th>
    <th>Helpdesk Response</th>
    <th>PIC Response</th>
    <th>Work</th>
    <th>Hold</th>
    <th>Leadtime</th>
    <th>Achieve SLA</th>
  </tr>
  <?php foreach ($tickets as $ticket) { ?>
    <tr>
      <td><?php echo $ticket['no'] ?></td>
      <td><?php echo $ticket['subject'] ?></td>
      <td><?php echo $ticket['description'] ?></td>
      <td><?php echo $ticket['company'] ?></td>
      <td><?php echo $ticket['company_branch'] ?></td>
      <td><?php echo $ticket['informer_name'] ?></td>
      <td><?php echo $ticket['date_open'] ?></td>
      <td><?php echo $ticket['date_close'] ?></td>
      <td><?php echo $ticket['status'] ?></td>
      <td><?php echo $ticket['category_head'] ?></td>
      <td><?php echo $ticket['category_sub'] ?></td>
      <td><?php echo $ticket['staff_name'] ?></td>
      <td><?php echo $ticket['sla'] ?></td>
      <td><?php echo $ticket['duration'] ?></td>
      <td><?php echo $ticket['response_helpdesk'] ?></td>
      <td><?php echo $ticket['response_pic'] ?></td>
      <td><?php echo $ticket['duration_work'] ?></td>
      <td><?php echo $ticket['duration_hold'] ?></td>
      <td><?php echo $ticket['duration_leadtime'] ?></td>
      <td><?php echo $ticket['is_achieve_sla'] ?></td>
    </tr>
  <?php } ?>
</table>