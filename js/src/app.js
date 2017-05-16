import React from 'react';
import ReactDOM from 'react-dom';
import ChatBox from './ChatBox.jsx';

function closeChat() {
  ReactDOM.unmountComponentAtNode(document.getElementById('chat-box'));
}

ReactDOM.render(
  <ChatBox closeChat={closeChat} defaultPosition={{bottom: 10, right: 10}} />,
  document.getElementById('chat-box')
);
