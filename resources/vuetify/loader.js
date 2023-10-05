import Vue from 'vue';
import Vuetify, {
    VApp, VContent, VSpacer, VIcon, VDivider,
    VNavigationDrawer, VAppBar, VAppBarNavIcon, VToolbar, VToolbarTitle,
    VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemContent, VListItemIcon, VListGroup, VListItemAvatar, VListItemAction, VListItemActionText,
    VBtn, VTooltip, VMenu, VAvatar,VAlert,
    VCard, VCardActions, VCardText, VCardTitle,
    VDialog, VContainer, VBadge, VChip,
    VDataTable, VEditDialog, VTreeview, VDataIterator,
    VTextField, VSwitch, VCheckbox, VAutocomplete, VSelect, VTextarea, VSnackbar, VCombobox, VRadioGroup, VRadio,
    VBreadcrumbs, VBreadcrumbsItem, VBreadcrumbsDivider, VFooter, VExpansionPanels, VExpansionPanel, VExpansionPanelHeader, VExpansionPanelContent,
    VRow, VCol,
    VTabs, VTab, VTabItem,
    VTimeline, VTimelineItem,
    VSlideXTransition, VDatePicker,
    VProgressLinear,
    VImg, VHover, VSubheader, VPagination, VFileInput,
    VSimpleTable, VOverlay,
} from 'vuetify/lib';

Vue.use(Vuetify, {
    components: {
        VApp, VContent, VSpacer, VIcon, VDivider,
        VNavigationDrawer, VAppBar, VAppBarNavIcon, VToolbar, VToolbarTitle,
        VList, VListItem, VListItemTitle, VListItemSubtitle, VListItemContent, VListItemIcon, VListGroup, VListItemAvatar, VListItemAction, VListItemActionText,
        VBtn, VTooltip, VMenu, VAvatar,VAlert,
        VCard, VCardActions, VCardText, VCardTitle,
        VDialog, VContainer, VBadge, VChip,
        VDataTable, VEditDialog, VTreeview, VDataIterator,
        VTextField, VSwitch, VCheckbox, VAutocomplete, VSelect, VTextarea, VSnackbar, VCombobox, VRadioGroup, VRadio,
        VBreadcrumbs, VBreadcrumbsItem, VBreadcrumbsDivider, VFooter, VExpansionPanels, VExpansionPanel, VExpansionPanelHeader, VExpansionPanelContent,
        VRow, VCol,
        VTabs, VTab, VTabItem,
        VTimeline, VTimelineItem,
        VSlideXTransition, VDatePicker,
        VProgressLinear,
        VImg, VHover, VSubheader, VPagination, VFileInput,
        VSimpleTable, VOverlay,
    }
});

window.vuetify =  new Vuetify({
    theme: {
        themes: {
            light: {
                primary: '#5867dd',
                success: '#34bfa3',
                warning: '#ffb822',
                error: '#f4516c',
                kallaGreen: '#007f3c',
                cictGreen: '#34bfa3'
            }
        }
    },
    icons: {
        iconfont: 'md',
    }
});

import './scss/main.scss'