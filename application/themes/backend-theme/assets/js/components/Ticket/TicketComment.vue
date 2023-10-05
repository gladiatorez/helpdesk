<template>
    <div>
        <template v-if="comments.length > 0">
            <v-list class="v-list--chat" dense color="grey lighten-4">
                <v-list-item v-for="comment in comments"
                    :key="comment.id">
                    <v-list-item-content class="pt-0" :class="{'text-right': userLoginId === comment.created_by}">
                        <v-list-item-title>
                            <div>
                                <div class="subtitle-2 font-weight-bold">
                                    {{ comment.created_by_staff ? comment.created_by_staff : comment.created_by_infomer }}
                                </div>
                                <div class="subtitle-2 font-weight-regular">
                                    <p class="text-wrap">
                                    {{ comment.comments }}
                                </p>
                                </div>
                            </div>
                        </v-list-item-title>
                        <v-list-item-subtitle class="font-weight-regular caption">
                            {{ $moment(comment.created_at).format('DD/MM/YYYY HH:mm:ss') }}
                        </v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>
            </v-list>

            <template v-if="totalRows > totalCurrent">
                <div class="d-flex justify-center pb-5">
                    <v-btn rounded text 
                        color="primary"
                        :loading="isLoadMore"
                        :disabled="isLoadMore"
                        @click="loadMore">
                        Load more
                    </v-btn>
                </div>
            </template>
        </template>
        <template v-else>
            <div class="text-center title font-weight-bold grey--text text--lighten-1">Chat is empty</div>
        </template>
    </div>
</template>

<script>
export default {
    name: 'TicketComment',
    props: {
        ticketId: {
            type: String,
            required: true,
        },
        items: {
            type: Array,
            required: true
        },
        totalRows: {
            type: Number,
            default: 0,
        },
        urlMore: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            comments: [],
            isLoadMore: false,
        }
    },
    computed: {
        total: {
            set(value) {
                this.$emit('input:totalRows', value)
            },
            get() {
                return this.totalRows
            }
        },
        userLoginId() {
            if (ufhy.user) {
                return ufhy.user.profile.userId;
            }

            return this.$store.state.userLogin.userId;
        },
        totalCurrent() {
            return this.comments.length;
        },
        lastCommentId() {
            if (this.totalCurrent > 0) {
                return this.comments[this.totalCurrent - 1].id;
            }

            return '0'
        }
    },
    watch: {
        items() {
            this.comments = this.items
        }
    },
    sockets: {
        pushComment(data) {
            if (data.ticket_id === this.ticketId) {
                this.comments.unshift(data);
            }
        }
    },
    methods: {
        loadMore() {
            if (this.isLoadMore) {
                return false;
            }

            this.isLoadMore = true;
            this.$axios.get(this.urlMore, { params: {id: this.lastCommentId, ticket: this.ticketId} }).then(response => {
                const { data } = response;
                this.total = data.total;
                if (data.rows.length > 0) {
                    const { rows } = data;
                    const that = this;
                    rows.forEach(row => {
                        that.comments.push(row);
                    });
                }

                this.isLoadMore = false;
            }).catch(error => {
                const {statusText, data} = error;
                if (typeof data.message !== "undefined") {
                    this.$coresnackbars.error(data.message);
                } else {
                    this.$coresnackbars.error(statusText);
                }
                this.isLoadMore = false;
            });
        }
    }
}
</script>

<style lang="scss" scoped>
.v-list--chat {
	.v-list-item__title > div {
		display: inline-block;
		// background-color: #ECEFF1;
		background-color: #ffffff;
		padding: 8px 12px;
		border-radius: 18px;
	}
	.v-list-item__subtitle {
		padding-left: 12px;
		padding-right: 12px;
	}
}
</style>