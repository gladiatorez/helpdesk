<template>
    <v-simple-table fixed-header class="table-countdown" height="100%" style="height: 100%">
        <template v-slot:default>
            <thead>
            <tr>
                <th
                    v-for="header in headers"
                    :key="header.value"
                    :class="{'text-right': header.value === 'countdown'}">
                    {{ header.text }}
                </th>
            </tr>
            </thead>
            <template v-for="item in items">
                <tbody :key="item.id">
                <tr>
                    <td>
                        <v-list-item class="pl-0 pr-0" style="min-height: 30px;">
                            <v-list-item-icon class="mr-2 my-3">
                                <v-icon>timer</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content class="py-2">
                                <v-list-item-title class="subtitle-2">
                                    <router-link
                                        class="primary--text hover-underline"
                                        :to="{
                                          name: 'dashboard.ticketDetail',
                                          params: {id: item.id}
                                        }">
                                        <span>{{ `#${item.number}` }}</span>
                                    </router-link>
                                </v-list-item-title>
                                <v-list-item-subtitle style="max-width:300px;">
                                    <span class="font-weight-bold">{{ item.subject }}</span>
                                </v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </td>

                    <td>
                        <template v-if="item.staff_v.length > 0">
                            <v-list-item class="pl-0 pr-0">
                                <v-list-item-icon class="mr-2 my-3">
                                    <v-icon>account_circle</v-icon>
                                </v-list-item-icon>
                                <v-list-item-content class="py-2">
                                    <v-list-item-title class="subtitle-2">
                                        {{ item.staff_v[0].full_name }}
                                    </v-list-item-title>
                                    <v-list-item-subtitle v-if="item.staff_v.length >= 2">
                                        {{ item.staff_v.length - 1 + ' More' }}
                                    </v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </template>
                    </td>

                    <td>
                        <div class="d-flex justify-end">
                            <template v-if="item.flag === 'HOLD'">
                                <core-flag-chip :flag="item.flag"></core-flag-chip>
                            </template>
                            <template v-else>
                                <countdown-timer
                                    :estimate="parseInt(item.estimate)"
                                    :working-time="parseInt(item.working_progress)"
                                />
                            </template>
                        </div>
                    </td>
                </tr>
                <tr v-if="item.notes && item.notes.length >= 1">
                    <td colspan="3">
                        <span class="text--secondary" style="font-size: 12px">{{ item.notes[0].description }}</span>
                    </td>
                </tr>
                </tbody>
            </template>
        </template>
    </v-simple-table>
</template>

<script>
import CountdownTimer from '../../../components/CountdownTimer.vue';
import CoreFlagChip from "../../../components/core/FlagChip";

export default {
    name: 'TicketCountdown',
    components: {
        CoreFlagChip,
        CountdownTimer
    },
    props: {
        progressCount: Number,
        holdCount: Number,
    },
    data() {
        return {
            loading: false,
            headers: [
                {value: 'number', text: 'Ticket'},
                {value: 'staff_v', text: 'Staff',},
                {value: 'countdown', text: 'Countdown', align: 'end', width: '130px'},
            ],
            items: []
        }
    },
    created() {
        this.fetchData()
    },
    sockets: {
        postingTicket(data) {
            this.fetchData()
        }
    },
    methods: {
        fetchData() {
            const that = this;
            this.loading = true;
            this.items = []
            this.$axios.get('dashboard/ticket_countdown').then(({data}) => {
                this.items = data.rows;
                this.$emit('update:progressCount', data.count_progress)
                this.$emit('update:holdCount', data.count_hold)
            }).catch(error => {
                console.log(error);
            }).then(() => {
                this.loading = false;
            })
        },
        counterTimer(countDownDate) {
            setInterval(function () {

                // Get today's date and time
                var now = new Date().getTime();

                // Find the distance between now and the count down date
                var distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display the result in the element with id="demo"
                document.getElementById("demo").innerHTML = days + "d " + hours + "h "
                    + minutes + "m " + seconds + "s ";

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("demo").innerHTML = "EXPIRED";
                }
            }, 1000)
        }
    }
}
</script>

<style lang="scss" scoped>
.table-countdown {
    &.theme--light.v-data-table {
        ::v-deep tbody tr:hover:not(.v-data-table__expanded__content):not(.v-data-table__empty-wrapper) {
            background: #FFFFFF;
        }

        ::v-deep tbody tr:last-child td:not(.v-data-table__mobile-row) {
            border-bottom: 1px solid #ebedf2;
        }

        ::v-deep tbody tr:not(:last-child) td:not(.v-data-table__mobile-row) {
            border-bottom: none;
        }
    }

    ::v-deep tbody tr:last-child td:not(.v-data-table__mobile-row) {
        height: 28px;
    }
}
</style>