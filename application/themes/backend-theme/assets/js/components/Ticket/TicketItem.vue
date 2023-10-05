<template>
    <v-card 
        v-bind="$attrs">
        <v-card-title class="pb-2 pt-3" style="line-height:1.2">
            <div>
                <div class="subtitle-2 font-weight-bold primary--text">#{{item.number}}</div>
                <div class='caption'>{{$moment(item.createdAt).format('DD/MM/YYYY HH:mm:ss')}}</div>
            </div>
            <v-spacer></v-spacer>
            <core-flag-chip :flag="item.flag"></core-flag-chip>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="py-3">
            <div style="max-height: 24px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" class="subtitle-2 font-weight-bold black--text">
                {{ item.subject }}
            </div>
            <div style="max-height: 24px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" class="subtitle-2 font-weight-regular">
                {{ item.descr }}
            </div>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions style="background: #fafafa; overflow-x: auto;">
            <div class="px-2">
                <core-priority-icon :priority-name="item.priorityName"></core-priority-icon>
            </div>
            <div v-if="item.informerFullName" class="px-2">
                <div class="overline">Sender</div>
                <div class="caption font-weight-bold">{{item.informerFullName}}</div>
            </div>
            <div class="px-2">
                <div class="overline">Company</div>
                <div class="caption font-weight-bold">{{item.companyName}}</div>
            </div>
            <div v-if="item.companyBranchName" class="px-2">
                <div class="overline">Location</div>
                <div class="caption font-weight-bold">{{item.companyBranchName}}</div>
            </div>
            <div v-if="item.categoryName" class="px-2">
                <div class="overline">Category</div>
                <div class="caption font-weight-bold">{{item.categoryName}}</div>
            </div>
        </v-card-actions>
    </v-card>
</template>

<script>
export default {
    name: 'TicketItem',
    props: {
        item: {
            type: Object,
            required: true
        }
    }
}
</script>