import React from 'react';
import ReactDOM from 'react-dom';
import ChatBox from './ChatBox.jsx';

function closeChat() {
  ReactDOM.unmountComponentAtNode(document.getElementById('chat-box'));
}

function renderApp() {
  ReactDOM.render(
    <ChatBox closeChat={closeChat} defaultPosition={{bottom: 10, right: 10}} />,
    document.getElementById('chat-box')
  );
}

if (typeof(sessionStorage) !== 'undefined' &&
    sessionStorage.getItem('chat_bot_state') !== null)
{
  renderApp();
} else {
  setTimeout(renderApp, delay*1000);
}
