<template>
    <v-card flat>
        <v-card-text>
            <v-row align="center" justify="start">
                <v-col cols="12" md="3" class="text-md-right">
                    Full Name
                </v-col>
                <v-col cols="12" md="9" class="black--text">
                    {{fullName}}
                </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-row align="center" justify="start">
                <v-col cols="12" md="3" class="text-md-right">
                    N.I.K
                </v-col>
                <v-col cols="12" md="9" class="black--text">
                    {{nik}}
                </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-row align="center" justify="start">
                <v-col cols="12" md="3" class="text-md-right">
                    Email
                </v-col>
                <v-col cols="12" md="9" class="black--text">
                    {{email}}
                </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-row align="center" justify="start">
                <v-col cols="12" md="3" class="text-md-right">
                   Phone / WA 
                </v-col>
                <v-col lg="4" class="pb-2">
                    <a :href="'https://wa.me/62' + phone.replace('+62','').replace(' ','').replace('-','').replace('0', '').replace('-','')"  style="display:inline-block; text-decoration: none" target="_blank">
                        <v-chip small>
                            <v-avatar left class="blue-grey" style="margin-left:-11px">
                                <v-icon  class="white--text">mdi-account</v-icon>
                            </v-avatar>
                            {{ phone }}
                        </v-chip>
                    </a>
                </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-row align="center" justify="start">
                <v-col cols="12" md="3" class="text-md-right">
                    Company
                </v-col>
                <v-col cols="12" md="9" class="black--text">
                    {{company}}
                </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-row align="center" justify="start">
                <v-col cols="12" md="3" class="text-md-right">
                    Location
                </v-col>
                <v-col cols="12" md="9" class="black--text">
                    {{location}}
                </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-row align="center" justify="start">
                <v-col cols="12" md="3" class="text-md-right">
                    Department
                </v-col>
                <v-col cols="12" md="9" class="black--text">
                    {{department}}
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>

<script>
export default {
    name: 'DetailSender',
    props: {
        fullName: String,
        nik: String,
        email: String,
        phone: String,
        company: String,
        location: String,
        department: String,
    },
}
</script>