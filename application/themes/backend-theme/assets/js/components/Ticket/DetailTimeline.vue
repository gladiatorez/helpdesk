<template>
    <v-timeline align-top dense class="ticket-timeline pt-0">
        <template v-for="(itemLog, index) in logs">
            <v-timeline-item 
                :key="index" 
                small fill-dot>
                <v-card>
                    <v-card-text class="pa-2">
                        <strong class="caption font-weight-bold black--text">{{ itemLog.event }}</strong><br/>
                        <div v-if="itemLog.reason" 
                            class="caption">
                            {{itemLog.reason}}
                        </div>
                        <div v-if="itemLog.userEvent" 
                            class="caption blue-grey--text d-flex">
                            <v-icon dense class="mr-1 blue-grey--text">person</v-icon>
                            <span>
                                {{ itemLog.userEvent.fullName }}
                            </span>
                        </div>
                        <div class="caption blue-grey--text d-flex">
                            <v-icon dense class="mr-1 blue-grey--text">access_time</v-icon>
                            <span>
                                {{$moment(itemLog.event_date).format('DD/MM/YYYY HH:mm')}}
                            </span>
                        </div>
                    </v-card-text>
                </v-card>
            </v-timeline-item>
        </template>
    </v-timeline>
</template>

<script>
export default {
    name: 'DetailTimeline',
    props: {
        logs: Array,
    },
}
</script>

<style lang="scss">
.ticket-timeline {
    &.v-timeline:before {
        background: transparent;
        border-right: 2px rgba(0, 0, 0, 0.12) dashed;
    }
    &.v-timeline--dense:not(.v-timeline--reverse):before {
        left: 10px;
    }

    .v-timeline-item {
        justify-content: flex-end;

        .v-timeline-item__divider {
            justify-content: start;
            min-width: 50px;
            padding-top: 8px;
        }

        .v-timeline-item__body {
            max-width: calc(100%);
        }

        .v-timeline-item__dot--small {
            height: 24px;
            width: 24px;
            left: 0;
            background: #f3f3f3;
            box-shadow: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .v-timeline-item__inner-dot {
            width: 16px;
            height: 16px;
            background: transparent!important;
            border: 2px solid;
        }
    }
}
</style>