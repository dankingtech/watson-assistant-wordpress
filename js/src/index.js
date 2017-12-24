import React from 'react';
import ReactDOM from 'react-dom';
import App from './App.jsx';

if (typeof localStorage !== 'undefined') {
    try {
        localStorage.setItem('localStorage', 1);
        localStorage.removeItem('localStorage');
    } catch (e) {
        Storage.prototype._setItem = Storage.prototype.setItem;
        Storage.prototype.setItem = function() {};
    }
}

function renderApp() {
  ReactDOM.render(
    <App
      title={settings.title}
      minimized={settings.minimized === 'yes'}
      isMobile={window.matchMedia("(max-width:768px)").matches}
      fullScreen={settings.full_screen === 'yes'}
      position={settings.position}
      callConfig={settings.call_config}
    />,
    document.getElementById('watsonconv-chat-box')
  );
}

if (typeof(sessionStorage) !== 'undefined' &&
    sessionStorage.getItem('chat_bot_state') !== null)
{
  renderApp();
} else {
  setTimeout(renderApp, settings.delay*1000);
}
