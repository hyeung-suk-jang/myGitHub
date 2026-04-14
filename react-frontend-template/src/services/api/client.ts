// ============================================================
// Axios Client
// ============================================================

import axios from 'axios';
import { axiosConfig } from './config';
import { setupInterceptors } from './interceptor';

const client = axios.create(axiosConfig);

setupInterceptors(client);

export default client;
