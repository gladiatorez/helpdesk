<template>
    <div>
        <template v-if="isExpired">
            <v-chip 
                color="error">
                <span class="subtitle-2">Expired</span>
            </v-chip>
        </template>
        <template v-else>
            <v-chip 
                :color="willExpired ? 'error' : 'primary'">
                <v-avatar tile
                    class="mr-1" 
                    style="min-width:30px!important; min-height:30px!important;">
                    <span class="subtitle-2">{{ `${days}d` }}</span>
                </v-avatar>
                <v-avatar tile
                    class="mr-1" 
                    style="min-width:30px!important; min-height:30px!important;">
                    <span class="subtitle-2">{{ `${hours}h` }}</span>
                </v-avatar>
                <v-avatar tile
                    class="mr-1" 
                    style="min-width:30px!important; min-height:30px!important;">
                    <span class="subtitle-2">{{ `${minutes}m` }}</span>
                </v-avatar>
                <v-avatar tile
                    class="mr-1"
                    style="min-width:30px!important; min-height:30px!important;">
                    {{ `${seconds}s` }}
                </v-avatar>
            </v-chip>
        </template>
    </div>
</template>

<script>
export default {
    name: 'CountdownTimer',
    props: {
        workingTime: {
            type: Number,
            required: true,
        }
    },
    data() {
        return {
            days: 0,
            hours: 0,
            minutes: 0,
            seconds: 0,
            interval: null,
            timeStart: 0,
            timeEnd: 0,
            isExpired: false,
            secondPlus: 0
        }
    },
    computed: {
        willExpired() {
            return this.hours <= 1
        }
    },
    mounted() {
        this.timerCount(this.workingTime);

        this.interval = setInterval(() => {
            this.timerCount(this.workingTime);
        }, 1000);
    },
    methods: {
        timerCount(working) {
            this.secondPlus++;
            const actual = working - this.secondPlus;
            if (actual < 0) {
                this.isExpired = true
                clearInterval(this.interval);
            }
            else {
                this.calcTime(actual);
            }
        },
        calcTime: function(dist){
            // Time calculations for days, hours, minutes and seconds
            this.days = Math.floor(dist / (3600*24));

            this.hours = Math.floor(dist % (3600*24) / 3600);
            this.minutes = Math.floor(dist % 3600 / 60);
            this.seconds = Math.floor(dist % 60);
        }
    }
}
</script>