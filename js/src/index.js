import 'core-js/fn/symbol';
import 'core-js/fn/promise';
import 'core-js/fn/object';
import 'core-js/es6/map';

import React from 'react';
import ReactDOM from 'react-dom';
import App from './App.jsx';
import ChatBox from './ChatBox.jsx';

if (typeof localStorage !== 'undefined') {
    try {
        localStorage.setItem('localStorage', 1);
        localStorage.removeItem('localStorage');
    } catch (e) {
        Storage.prototype._setItem = Storage.prototype.setItem;
        Storage.prototype.setItem = function() {};
    }
}

let shortcodeDiv = document.getElementById('watsonconv-inline-box');
let floatDiv = document.getElementById('watsonconv-floating-box');

function renderFloatingBox() {
  ReactDOM.render(
    <App isMobile={window.matchMedia("(max-width:640px)").matches} />,
    floatDiv
  );
}

if (shortcodeDiv) {
  ReactDOM.render(
    <ChatBox isMinimized={false} />,
    shortcodeDiv
  );
} else if (typeof(sessionStorage) !== 'undefined' &&
    sessionStorage.getItem('chat_bot_state') !== null)
{
  renderFloatingBox();
} else {
  setTimeout(renderFloatingBox, watsonconvSettings.delay*1000);
}
