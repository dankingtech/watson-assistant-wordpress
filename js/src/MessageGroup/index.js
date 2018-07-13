import React from 'react';
import DelayedGroup from './DelayedGroup.jsx';
import SimpleGroup from './SimpleGroup.jsx';

const MessageGroup = (props) => (
  (props.from == 'watson') 
    ? <DelayedGroup {...props} /> : <SimpleGroup {...props} />
)

export default MessageGroup;