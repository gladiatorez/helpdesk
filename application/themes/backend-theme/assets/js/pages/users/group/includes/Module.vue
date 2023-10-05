<template>
  <v-card class="elevation-base mb-4">
    <v-card-title class="border-bottom">
      <input
        class="custom-control-input checkbox-module"
        type="checkbox"
        :name="'modules['+ moduleRole.slug +']'"
        :id="moduleRole.slug"
        :value="moduleRole.slug"
        @change="selectedAll(moduleRole, $event)"
      >
      <label :for="moduleRole.slug" class="ml-3 primary--text">
        {{ 'Module: ' + moduleRole.name }}
      </label>
    </v-card-title>

    <v-card-text class="pa-4">
      <permission-module-roles
        inline
        v-if="moduleRole.roles.length"
        ref="permissionModuleRoles"
        :roles="moduleRole.roles"
        :module="moduleRole"
        :data-roles="getModuleRoles"
        @indeterminate="setIndeterminate"
        @unchecked="setUnchecked"
        @checked="setChecked"
      ></permission-module-roles>

      <v-container fluid v-if="moduleRole.sections.length" class="pa-0">
        <v-row>
          <template v-for="(section, sectionIndex) in moduleRole.sections">
            <v-col cols="12" md="4" :key="sectionIndex">
              <div class="custom-control custom-checkbox mb-2">
                <input
                    class="custom-control-input checkbox-section"
                    type="checkbox"
                    :name="'module_roles['+ moduleRole.slug +'][sections]['+ section.slug +']'"
                    :id="'module_' + moduleRole.slug + '_' + section.slug"
                    :value="section.slug"
                    v-model.lazy="sectionSelected"
                    @change="sectionSelectedAll(moduleRole, section, $event)"
                  >
                <label 
                  class="custom-control-label font-weight-bold" 
                  :for="'module_' + moduleRole.slug + '_' + section.slug" v-text="section.name"></label>
              </div>
              <div class="ml-3">
                <permission-module-section-roles
                  v-if="section.roles.length"
                  :ref="'permissionModuleSectionRoles_' + section.slug"
                  :roles="section.roles"
                  :data-roles="getDataSectionRoles(section.slug)"
                  :module="moduleRole"
                  :section="section"
                  @indeterminate="setSectionIndeterminate"
                  @unchecked="setSectionUnchecked"
                  @checked="setSectionChecked"
                ></permission-module-section-roles>
              </div>
            </v-col>
          </template>
        </v-row>
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script>
  import PermissionModuleRoles from './ModuleRoles.vue';
  import PermissionModuleSectionRoles from './ModuleSectionRoles.vue';
  
  Array.prototype.remove = function() {
    var what, a = arguments, L = a.length, ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }
    return this;
  };

  export default {
    name: 'permissions-module',
    components: {
      PermissionModuleRoles, PermissionModuleSectionRoles
    },
    props: {
      moduleRole: {
        type: Object,
        required: true
      },
      dataRoles: {
        type: Object,
        required: true
      },
    },
    data() {
      return {
        sectionSelected: [],
      }
    },
    computed: {
      sectionValues() {
        return this.moduleRole.sections.map(section => section.slug);
      },
      statusSectionCheck() {
        if (this.sectionSelected.length === 0) {
          return 'unchecked';
        }

        if (this.sectionSelected.length === this.sectionValues.length) {
          return 'checked';
        }

        return 'indeterminate';
      },
      checkboxModule() {
        return this.$el.querySelector('.checkbox-module');
      },
      getModuleRoles() {
        if (typeof this.dataRoles[this.moduleRole.slug] !== "undefined" && Array.isArray(this.dataRoles[this.moduleRole.slug])) {
          return this.dataRoles[this.moduleRole.slug];
        }

        return [];
      },
    },
    watch: {
      statusSectionCheck: {
        handler(value) {
          switch(value) {
            case 'indeterminate':
              this.setIndeterminate();
            break;
            case 'checked':
              this.setChecked();
            break;
            default:
              this.setUnchecked();
          }
        },
      }
    },
    mounted() {
      this.init();
    },
    methods: {
      init() {
        // set default permissions
        if (this.moduleRole.slug === "dashboard") {
          this.checkboxModule.checked = true;
        }
      },
      getDataSectionRoles(sectionSlug) {
        const dataRoles = this.dataRoles[this.moduleRole.slug];
        if (typeof dataRoles !== "undefined") {
          return typeof dataRoles[sectionSlug] !== "undefined"
            ? dataRoles[sectionSlug]
            : [];
        }
        return [];
      },
      checkboxSection(moduleSlug, sectionSlug) {
        return this.$el.querySelector('input[name="module_roles['+moduleSlug+'][sections]['+sectionSlug+']"]');
      },
      setIndeterminate() {
        this.checkboxModule.checked = false
        this.checkboxModule.indeterminate = true;
        
        this.$emit('update');
      },
      setUnchecked() {
        this.checkboxModule.checked = false
        this.checkboxModule.indeterminate = false;
        this.$emit('update');
      },
      setChecked() {
        this.checkboxModule.checked = true;
        this.checkboxModule.indeterminate = false;
        this.$emit('update');
      },
      selectedAll(module) {
        if (module.roles.length) {
          return this.checkboxModule.checked 
            ? this.$refs.permissionModuleRoles.setCheckedAll()
            : this.$refs.permissionModuleRoles.setUncheckedAll();
        } else if (module.sections.length) {
          this.checkboxModule.checked 
            ? this.sectionSelected = this.sectionValues
            : this.sectionSelected = [];
          this.$nextTick(() => {
            module.sections.forEach(section => {
              this.sectionSelectedAll(module, section);
            });
          });
        }
      },
      setSectionIndeterminate(payload) {
        const checkboxSection = this.checkboxSection(payload.module.slug, payload.section.slug);
        checkboxSection.checked = false;
        checkboxSection.indeterminate = true;
        this.sectionSelected.remove(payload.section.slug);
        this.$emit('update');
      },
      setSectionUnchecked(payload) {
        const checkboxSection = this.checkboxSection(payload.module.slug, payload.section.slug);
        checkboxSection.checked = false;
        checkboxSection.indeterminate = false;
        this.sectionSelected.remove(payload.section.slug);
        this.$emit('update');
      },
      setSectionChecked(payload) {
        const checkboxSection = this.checkboxSection(payload.module.slug, payload.section.slug);
        checkboxSection.checked = true;
        checkboxSection.indeterminate = false;
        
        if (this.sectionSelected.filter(selected => selected === payload.section.slug).length <= 0) {
          this.sectionSelected.push(payload.section.slug);
        }
        
        this.$emit('update');
      },
      sectionSelectedAll(module, section) {
        const checkboxSection = this.checkboxSection(module.slug, section.slug);
        const componentRefs = 'permissionModuleSectionRoles_' + section.slug;
        if (this.$refs[componentRefs].length) {
          this.$refs[componentRefs].forEach(ref => {
            checkboxSection.checked 
              ? ref.setCheckedAll()
              : ref.setUncheckedAll()
          });
        }
      },
    }
  }
</script>