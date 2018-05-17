import React from 'react';
import DelayedGroup from './DelayedGroup.jsx';
import SimpleGroup from './SimpleGroup.jsx';

const MessageGroup = (props) => (
  (watsonconvSettings.typingDelay === 'yes' && props.message.from == 'watson') 
    ? <DelayedGroup {...props} /> : <SimpleGroup {...props} />
)

export default MessageGroup;