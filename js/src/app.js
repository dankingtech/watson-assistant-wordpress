import React from 'react';
import ReactDOM from 'react-dom';
import ChatBox from './ChatBox.jsx';

function renderApp() {
  ReactDOM.render(
    <ChatBox
      title={settings.title}
      minimized={settings.minimized}
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
