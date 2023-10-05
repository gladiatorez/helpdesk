<template>
  <div>
    <div 
      v-for="(role, index) in roles" 
      :class="{'custom-control custom-checkbox': true, 'custom-checkbox': true, 'custom-control-inline': inline}"
      :key="index"
    >
      <input 
        class="custom-control-input" 
        type="checkbox" 
        :name="'module_roles[' + module.slug + '][roles][' + role.value + ']'" 
        :id="'module_' + module.slug + '_' + role.value" 
        :value="role.value"
        v-model="selected"
      >
      <label 
        class="custom-control-label" 
        :for="'module_' + module.slug + '_' + role.value"
        v-text="role.text"></label>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'permissions-module-roles',
    props: {
      module: {
        type: Object,
        required: true
      },
      roles: {
        type: Array,
        required: true
      },
      inline: {
        type: Boolean,
        default: false
      },
      dataRoles: {
        type: Array,
        required: true
      },
    },
    data() {
      return {
        selected: []
      }
    },
    computed: {
      roleValues() {
        return this.roles.map(role => role.value );
      },
      statusCheck() {
        if (this.selected.length === 0) {
          return 'unchecked';
        }

        const role = this.roleValues;
        if (this.selected.length === role.length) {
          return 'checked';
        }

        return 'indeterminate';
      }
    },
    watch: {
      statusCheck: {
        handler() {
          this.$emit(this.statusCheck);
        },
      }
    },
    mounted() {
      this.selected = this.dataRoles;
    },
    methods: {
      setCheckedAll() {
        this.selected = this.roleValues;
      },
      setUncheckedAll() {
        this.selected = [];
      }
    }
  }
</script>