import errors from '../pages/errors/router';
import Dashboard from '../pages/dashboard/router';
import References from '../pages/references/router';
import Staff from '../pages/staff/router';
import Users from '../pages/users/router';
import Faq from '../pages/faq/router';
import Tickets from '../pages/tickets/router';
import Queues from '../pages/queues/router';
import Informer from '../pages/informer/router';
import Reports from '../pages/reports/router';
import Profile from '../pages/profile/router';
import Recomendations from '../pages/recomendations/router';
import Educations from '../pages/educations/router';

export default [
  {
    path: '/',
    redirect: '/dashboard',
  },
  errors, 
  Dashboard,
  References, 
  Staff, 
  Users, 
  Faq,
  Tickets,
  Informer,
  Queues,
  Reports,
  Profile,
  Recomendations,
  Educations
];
