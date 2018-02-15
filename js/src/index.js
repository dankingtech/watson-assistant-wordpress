import 'core-js/fn/symbol';
import 'core-js/fn/promise';
import 'core-js/fn/object';
import 'core-js/es6/map';

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
    <App isMobile={window.matchMedia("(max-width:768px)").matches} />,
    document.getElementById('watsonconv-chat-box')
  );
}

if (typeof(sessionStorage) !== 'undefined' &&
    sessionStorage.getItem('chat_bot_state') !== null)
{
  renderApp();
} else {
  setTimeout(renderApp, watsonconvSettings.delay*1000);
}
