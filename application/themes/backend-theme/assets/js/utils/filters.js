import { sprintf } from 'sprintf-js';
import moment from 'moment';
import store from '../store';

export default {
	renderStatus (status) {
		const statusText = status === 'A' ? 'lb::active' : 'lb::disabled'
		return sprintf(
			'<span class="badge %s">%s</span>',
			status === 'A' ? 'badge-success' : 'badge-secondary',
			store.getters['localisation/lang'](statusText)
		)
	},
	renderDate(date) {
		if (date) {
			return sprintf(
				'<span title="DD MMM YYYY">%s</span>',
				moment(date, 'YYYY-MM-DD').format('DD MMM YYYY')
			);
		}
		return '';
	},
	renderDateTime(dateTime) {
		if (dateTime) {
			return sprintf(
				'<span title="DD/MM/YYYY HH:mm:ss">%s</span>',
				moment(dateTime, 'YYYY-MM-DD HH:mm:ss').format('DD/MM/YYYY HH:mm:ss')
			);
		}
		return '';
	},
}