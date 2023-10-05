<template>
    <tr>
      <td>{{ fullName }}</td>
      <td>{{ cnt }}</td>
    </tr>
</template>

<script>
export default {
  name: 'ticket-staff-table-child',
  props: {
    fullName: String,
    cnt: Number
  }
}
</script>
