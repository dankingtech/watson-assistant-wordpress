import React, { Component } from 'react';

export default class SimpleGroup extends Component {
  shouldComponentUpdate() {
    return false;
  }

  render({sendMessage, message: {from, text, options}}) {
    let response = [], responseOptions = '';

    response = text.map((message, index) => (
      <div
        key={index}
        className={`message ${from}-message watson-font`}
        dangerouslySetInnerHTML={{__html: message}}
      ></div>
    ));


    if (Array.isArray(options)) {
      responseOptions = options.map((option, index) => (
        <div 
          key={text.length + index} className={`message message-option watson-font`} 
          onClick={() => { sendMessage(option); }}
        >
          {option}
        </div>
      ));
    }
      
    return <div>
      {response}
      {responseOptions}
    </div>;
  }
}
