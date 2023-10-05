import Vue from 'vue';

import JoinForm from './components/JoinForm.vue';
Vue.component('join-form', JoinForm);

/*VUE.$on('submit-join-form', function() {
	if (VUE.$refs.companyId.value != VUE.$refs.company.internalValue) {
		VUE.$refs.companyId.value = VUE.$refs.company.internalValue;
	}
	if (VUE.$refs.departmentId.value != VUE.$refs.department.internalValue) {
		VUE.$refs.departmentId.value = VUE.$refs.department.internalValue;
	}
	VUE.$refs.joinForm.submit();
});*/