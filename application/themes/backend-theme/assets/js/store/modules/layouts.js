const state = {
    siteName: '',
    siteNameAbbr: '',
    drawer: true,
    miniDrawer: false,
    menuItems: {}
}

const mutations = {
    setSiteName(state, payload) {
        state.siteName = payload;
    },
    setSiteNameAbbr(state, payload) {
        state.siteNameAbbr = payload;
    },
    setMenuItems(state, payload) {
        state.menuItems = payload
    },
    setDrawer(state, payload) {
        state.drawer = payload
    },
    setMiniDrawer(state, payload) {
        state.miniDrawer = payload
    },
    toggleDrawer(state) {
        state.drawer = !state.drawer
    },
    toggleMiniDrawer(state) {
        state.miniDrawer = !state.miniDrawer
    }
}

export default {
    namespaced: true,
    state, 
    mutations
}