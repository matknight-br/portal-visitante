import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';
import './index.css';

const params = new URLSearchParams(window.location.search);
(window as any).wifiParams = {
  clientMac: params.get('clientMac') || '',
  apMac: params.get('apMac') || '',
  ssidName: params.get('ssidName') || '',
  radioId: params.get('radioId') || '1',
  clientIp: params.get('clientIp') || '',
  site: params.get('site') || 'default',
};

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
