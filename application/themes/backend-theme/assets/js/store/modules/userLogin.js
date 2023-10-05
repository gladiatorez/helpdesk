const state = {
    userId: '',
    fullName: '',
    position: '',
}

const mutations = {
    setUserId(state, payload) {
        state.userId = payload;
    },
    setFullName(state, payload) {
        state.fullName = payload;
    },
    setPosition(state, payload) {
        state.position = payload;
    },
}

export default {
    namespaced: true,
    state, 
    mutations
}