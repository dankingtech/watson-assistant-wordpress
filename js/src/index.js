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
      minimized={settings.minimized}
      isMobile={window.matchMedia("(max-width:768px)").matches}
      position={settings.position}
    />,
    document.getElementById('chat-box')
  );
}

if (typeof(sessionStorage) !== 'undefined' &&
    sessionStorage.getItem('chat_bot_state') !== null)
{
  renderApp();
} else {
  setTimeout(renderApp, settings.delay*1000);
}
