import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { Provider } from 'react-redux';
import { store } from '@/store';
import App from './App';
import '@/styles/index.scss';

const root = document.getElementById('root');

if (!root) {
  throw new Error('Root element not found. Make sure <div id="root"> exists in index.html.');
}

createRoot(root).render(
  <StrictMode>
    <Provider store={store}>
      <App />
    </Provider>
  </StrictMode>,
);
