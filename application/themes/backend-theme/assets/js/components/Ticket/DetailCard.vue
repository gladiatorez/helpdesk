<template>
    <v-card>
        <v-card-title>
            <div class="subheading font-weight-light">{{subject}}</div>
            <v-spacer></v-spacer>
            <core-flag-chip :flag="flag"></core-flag-chip>
            <v-chip color="primary" outlined small class="ml-2">#{{no}}</v-chip>
        </v-card-title>
        <v-divider />
        <v-card-title>
            <core-avatar-initial 
                class="mr-3"
                color="blue-grey darken-3"
                text-color="white--text headline"
                :title="informerFullName"
            />
            <div>
                <div class="body-2">
                    {{informerFullName}}
                    <small class="grey--text text--lighten-1">&lt; {{companyName}} \&gt;</small>
                    <small class="grey--text text--lighten-1">&lt; {{companyBranchName}} \&gt;</small>
                </div>
                <div class="subtitle-2 grey--text text--lighten-1">{{informerEmail}}</div>
            </div>
            <v-spacer></v-spacer>
            <div class="subtitle-2 grey--text text--darken-1 text-right">
                <strong>{{$moment(createdAt).format('HH:mm')}}</strong><br/>
                {{$moment(createdAt).format('DD/MM/YYYY')}}
            </div>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="black--text">
            {{description}}
        </v-card-text>
        <v-card-text>
            <div v-if="attachment">
                <strong>Attachment</strong><br/>
                <template v-for="(attachment, indexAttachment) in attachment">
                    <a :href="baseUrl + 'files/download/' + attachment.file_id" :key="indexAttachment" style="display:inline-block; text-decoration: none" target="_blank">
                        <v-chip small>
                            <v-avatar left class="blue-grey" style="margin-left:-11px">
                                <v-icon dense class="white--text">attach_file</v-icon>
                            </v-avatar>
                            Lampiran {{indexAttachment + 1}}
                        </v-chip>
                    </a>
                </template>
            </div>
        </v-card-text>
    </v-card>
</template>

<script>
export default {
    name: 'TicketDetailCard',
    props: {
        subject: String,
        flag: String,
        no: String,
        informerFullName: String,
        informerEmail: String,
        companyName: String,
        companyBranchName: String,
        createdAt: String,
        description: String,
        attachment: Array,
    },
    computed: {
        baseUrl() {
            return BASE_URL;
        }
    }
}
</script>