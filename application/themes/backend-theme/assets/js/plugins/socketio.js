import Vue from 'vue';
import io from 'socket.io-client';
import VueSocketIO from 'vue-socket.io';

const { protocol, hostname } = window.location;
const connectionUrl = protocol + '//' + hostname + ':20401';

Vue.use(new VueSocketIO({
    debug: ENV === 'development',
    connection: io(connectionUrl, { secure: true, rejectUnauthorized: false, reconnection: true  })
}));
