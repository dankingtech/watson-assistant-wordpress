import React from 'react';
import WatsonMessage from './WatsonMessage.jsx';
import UserMessage from './UserMessage.jsx';

const MessageGroup = (props) => (
  (props.from == 'watson') 
    ? <WatsonMessage {...props} /> : <UserMessage {...props} />
)

export default MessageGroup;