<template>
    <div>
        <template v-if="value === 'A'">
            <v-chip color="#716aca" text-color="white" small>
                {{$t('lb::active')}}
            </v-chip>
        </template>
        <template v-else>
            <v-chip color="#c4c5d6" text-color="white" small>
                {{$t('lb::disabled')}}
            </v-chip>
        </template>
    </div>
</template>

<script>
export default {
    name: 'CoreStatusValue',
    props: ['value']
}
</script>
